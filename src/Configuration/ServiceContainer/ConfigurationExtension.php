<?php

namespace Meteor\Configuration\ServiceContainer;

use Meteor\Configuration\ConfigurationWriter;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConfigurationExtension extends ExtensionBase implements ExtensionInterface
{
    // NB: Set in Application
    const SERVICE_LOADER = 'configuration.loader';
    const SERVICE_WRITER = 'configuration.writer';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'configuration';
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
        $this->loadConfigurationWriter($container);
    }

    private function loadConfigurationWriter(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_WRITER, new Definition(ConfigurationWriter::class))->setPublic(true);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
