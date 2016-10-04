<?php

namespace Meteor\Cli\ServiceContainer;

use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CliExtension extends ExtensionBase implements ExtensionInterface
{
    const PARAMETER_COMMAND_SERVICE_IDS = 'cli.command.service_ids';
    const SERVICE_APPLICATION = 'cli.application';
    const SERVICE_INPUT = 'cli.input';
    const SERVICE_OUTPUT = 'cli.output';
    const TAG_COMMAND = 'cli.command';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'cli';
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
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $serviceIds = array_keys($container->findTaggedServiceIds(self::TAG_COMMAND));
        $container->setParameter(self::PARAMETER_COMMAND_SERVICE_IDS, $serviceIds);
    }
}
