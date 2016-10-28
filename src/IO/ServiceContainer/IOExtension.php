<?php

namespace Meteor\IO\ServiceContainer;

use Meteor\Cli\ServiceContainer\CliExtension;
use Meteor\Logger\ServiceContainer\LoggerExtension;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class IOExtension extends ExtensionBase implements ExtensionInterface
{
    const SERVICE_IO = 'io';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'io';
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
        $container->setDefinition(self::SERVICE_IO, new Definition('Meteor\IO\ConsoleIO', [
            new Reference(CliExtension::SERVICE_INPUT),
            new Reference(CliExtension::SERVICE_OUTPUT),
            new Reference(LoggerExtension::SERVICE_LOGGER),
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
