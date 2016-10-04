<?php

namespace Meteor\Package\Provider\GoogleDrive\ServiceContainer;

use Meteor\IO\ServiceContainer\IOExtension;
use Meteor\Package\ServiceContainer\PackageExtension;
use Meteor\Process\ServiceContainer\ProcessExtension;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class GoogleDrivePackageProviderExtension extends ExtensionBase implements ExtensionInterface
{
    const PROVIDER_NAME = 'gdrive';
    const PARAMETER_BINARY = 'gdrive_package_provider.binary';
    const PARAMETER_FOLDERS = 'gdrive_package_provider.folders';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'gdrive_package_provider';
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
                ->scalarNode('binary')
                    ->defaultValue('gdrive')
                ->end()
                ->arrayNode('folders')
                    ->normalizeKeys(false)
                    ->defaultValue(array(
                        'jadu/cms' => '0B3tlQeNsllCKY2tzbFpUUkI2OGM',
                        'jadu/xfp' => '0B2h2-RgE2WidOHRhZVNUbUc1Z0E',
                    ))
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
     * @param ContailerBuilder $container
     * @param array $config
     */
    private function loadProvider(ContainerBuilder $container, array $config)
    {
        $container->setParameter(self::PARAMETER_BINARY, $config['binary']);
        $container->setParameter(self::PARAMETER_FOLDERS, $config['folders']);

        $definition = new Definition('Meteor\Package\Provider\GoogleDrive\GoogleDrivePackageProvider', array(
            new Reference(ProcessExtension::SERVICE_PROCESS_RUNNER),
            new Reference(IOExtension::SERVICE_IO),
            '%'.self::PARAMETER_BINARY.'%',
            '%'.self::PARAMETER_FOLDERS.'%',
        ));
        $container->setDefinition(PackageExtension::SERVICE_PROVIDER_PREFIX.'.'.self::PROVIDER_NAME, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
