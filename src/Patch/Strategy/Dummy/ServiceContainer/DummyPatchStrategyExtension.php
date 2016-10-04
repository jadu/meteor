<?php

namespace Meteor\Patch\Strategy\Dummy\ServiceContainer;

use Meteor\Patch\ServiceContainer\PatchExtension;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class DummyPatchStrategyExtension extends ExtensionBase implements ExtensionInterface
{
    const STRATEGY_NAME = 'dummy';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'dummy_patch_strategy';
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
        if ($container->getParameter(PatchExtension::PARAMETER_STRATEGY) !== self::STRATEGY_NAME) {
            return;
        }

        $this->loadStrategy($container);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadStrategy(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Patch\Strategy\Dummy\DummyPatchStrategy');
        $container->setDefinition(PatchExtension::SERVICE_STRATEGY_PREFIX.'.'.self::STRATEGY_NAME, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
