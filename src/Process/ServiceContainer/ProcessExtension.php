<?php

namespace Meteor\Process\ServiceContainer;

use Meteor\IO\ServiceContainer\IOExtension;
use Meteor\Process\MemoryLimitSetter;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ProcessExtension extends ExtensionBase implements ExtensionInterface
{
    const SERVICE_PROCESS_RUNNER = 'process.runner';
    const SERVICE_MEMORY_LIMIT_SETTER = 'process.memory_limit_setter';
    const SERVICE_PROCESS_FACTORY = 'process.factory';

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
        $this->loadMemoryLimitSetter($container);
        $this->loadProcessFactory($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadProcessRunner(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_PROCESS_RUNNER, new Definition('Meteor\Process\ProcessRunner', [
            new Reference(IOExtension::SERVICE_IO),
            new Reference(self::SERVICE_MEMORY_LIMIT_SETTER),
            new Reference(self::SERVICE_PROCESS_FACTORY),
        ]));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadMemoryLimitSetter(ContainerBuilder $container)
    {
        $container->setDefinition(
            self::SERVICE_MEMORY_LIMIT_SETTER,
            new Definition('Meteor\Process\MemoryLimitSetter')
        );
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadProcessFactory(ContainerBuilder $container)
    {
        $container->setDefinition(
            self::SERVICE_PROCESS_FACTORY,
            new Definition('Meteor\Process\ProcessFactory')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
