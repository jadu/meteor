<?php

namespace Meteor\Process\ServiceContainer;

use Meteor\IO\ServiceContainer\IOExtension;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ProcessExtension extends ExtensionBase implements ExtensionInterface
{
    public const SERVICE_PROCESS_RUNNER = 'process.runner';
    public const SERVICE_PROCESS_FACTORY = 'process.factory';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'process';
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
        $this->loadProcessRunner($container);
        $this->loadProcessFactory($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadProcessRunner(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_PROCESS_RUNNER, new Definition('Meteor\Process\ProcessRunner', [
            new Reference(IOExtension::SERVICE_IO),
            new Reference(self::SERVICE_PROCESS_FACTORY),
        ]))
        ->setPublic(true);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadProcessFactory(ContainerBuilder $container)
    {
        $container->setDefinition(
            self::SERVICE_PROCESS_FACTORY,
            new Definition('Meteor\Process\ProcessFactory')
        )
        ->setPublic(true);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
