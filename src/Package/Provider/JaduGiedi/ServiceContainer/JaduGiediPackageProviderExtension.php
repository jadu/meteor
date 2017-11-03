<?php

namespace Meteor\Package\Provider\JaduGiedi\ServiceContainer;

use GuzzleHttp\Client;
use Meteor\IO\ServiceContainer\IOExtension;
use Meteor\Package\ServiceContainer\PackageExtension;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class JaduGiediPackageProviderExtension extends ExtensionBase implements ExtensionInterface
{
    const PROVIDER_NAME = 'giedi';
    const PARAMETER_BASE_URLS = 'giedi_package_provider.base_urls';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'giedi_package_provider';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('base_urls')
                    ->normalizeKeys(false)
                    ->defaultValue([
                        'jadu/cms' => 'http://giedi.hq.jadu.net/packages/cms/',
                        'jadu/xfp' => 'http://giedi.hq.jadu.net/packages/xfp/',
                    ])
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        if ($container->getParameter(PackageExtension::PARAMETER_PROVIDER) !== self::PROVIDER_NAME) {
            return;
        }

        $this->loadProvider($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function loadProvider(ContainerBuilder $container, array $config)
    {
        $container->setParameter(self::PARAMETER_BASE_URLS, $config['base_urls']);

        $definition = new Definition('Meteor\Package\Provider\JaduGiedi\JaduGiediPackageProvider', [
            new Reference(IOExtension::SERVICE_IO),
            '%' . self::PARAMETER_BASE_URLS . '%',
            new Client(['verify' => false,'allow_redirects' => false])
        ]);
        $container->setDefinition(PackageExtension::SERVICE_PROVIDER_PREFIX . '.' . self::PROVIDER_NAME, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
