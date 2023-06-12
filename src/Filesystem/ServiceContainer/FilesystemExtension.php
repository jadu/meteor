<?php

namespace Meteor\Filesystem\ServiceContainer;

use Meteor\Filesystem\Filesystem;
use Meteor\Filesystem\Finder\FinderFactory;
use Meteor\IO\ServiceContainer\IOExtension;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class FilesystemExtension extends ExtensionBase implements ExtensionInterface
{
    const SERVICE_FILESYSTEM = 'filesystem';
    const SERVICE_FINDER_FACTORY = 'filesystem.finder.factory';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'filesystem';
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
        $this->loadFinderFactory($container);
        $this->loadFilesystem($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadFinderFactory(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_FINDER_FACTORY, new Definition(FinderFactory::class))->setPublic(true);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadFilesystem(ContainerBuilder $container)
    {
        $container->setDefinition(
            self::SERVICE_FILESYSTEM,
            new Definition(Filesystem::class, [
                new Reference(self::SERVICE_FINDER_FACTORY),
                new Reference(IOExtension::SERVICE_IO),
            ])
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
