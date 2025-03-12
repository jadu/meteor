<?php

namespace Meteor\Logger\ServiceContainer;

use Meteor\Logger\Logger;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class LoggerExtension extends ExtensionBase implements ExtensionInterface
{
    public const SERVICE_LOGGER = 'logger';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'logger';
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
        $container->setDefinition(self::SERVICE_LOGGER, new Definition(Logger::class))->setPublic(true);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
