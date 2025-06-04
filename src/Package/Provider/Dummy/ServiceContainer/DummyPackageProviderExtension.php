<?php

namespace Meteor\Package\Provider\Dummy\ServiceContainer;

use Meteor\Package\ServiceContainer\PackageExtension;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class DummyPackageProviderExtension extends ExtensionBase implements ExtensionInterface
{
    public const PROVIDER_NAME = 'dummy';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'dummy_package_provider';
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
        $definition = new Definition('Meteor\Package\Provider\Dummy\DummyPackageProvider');
        $container->setDefinition(PackageExtension::SERVICE_PROVIDER_PREFIX . '.' . self::PROVIDER_NAME, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
