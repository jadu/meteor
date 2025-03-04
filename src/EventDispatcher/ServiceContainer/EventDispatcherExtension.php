<?php

namespace Meteor\EventDispatcher\ServiceContainer;

use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class EventDispatcherExtension extends ExtensionBase implements ExtensionInterface
{
    public const SERVICE_EVENT_DISPATCHER = 'events.event_dispatcher';
    public const TAG_SUBSCRIBER = 'events.subscriber';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'events';
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
        $this->loadEventDispatcher($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadEventDispatcher(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_EVENT_DISPATCHER, new Definition('Symfony\Component\EventDispatcher\EventDispatcher'));
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(self::SERVICE_EVENT_DISPATCHER);

        foreach ($container->findTaggedServiceIds(self::TAG_SUBSCRIBER) as $id => $tags) {
            $definition->addMethodCall('addSubscriber', [new Reference($id)]);
        }
    }
}
