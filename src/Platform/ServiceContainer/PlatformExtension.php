<?php

namespace Meteor\Platform\ServiceContainer;

use Meteor\Filesystem\ServiceContainer\FilesystemExtension;
use Meteor\Process\ServiceContainer\ProcessExtension;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class PlatformExtension extends ExtensionBase implements ExtensionInterface
{
    const SERVICE_PLATFORM = 'platform';
    const SERVICE_PLATFORM_UNIX = 'platform.unix';
    const SERVICE_PLATFORM_WINDOWS = 'platform.windows';
    const SERVICE_UNIX_INSTALL_CONFIG_LOADER = 'platform.unix.install_config_loader';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'platform';
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
        $this->loadWindowsPlatform($container);
        $this->loadUnixPlatform($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadWindowsPlatform(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_PLATFORM_WINDOWS, new Definition('Meteor\Platform\Windows\WindowsPlatform', array(
            new Reference(ProcessExtension::SERVICE_PROCESS_RUNNER),
        )));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadUnixPlatformInstallConfigLoader(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_UNIX_INSTALL_CONFIG_LOADER, new Definition('Meteor\Platform\Unix\InstallConfigLoader'));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadUnixPlatform(ContainerBuilder $container)
    {
        $this->loadUnixPlatformInstallConfigLoader($container);

        $definition = new Definition('Meteor\Platform\Unix\UnixPlatform', array(
            new Reference(self::SERVICE_UNIX_INSTALL_CONFIG_LOADER),
            new Reference(FilesystemExtension::SERVICE_FILESYSTEM),
        ));
        $container->setDefinition(self::SERVICE_PLATFORM_UNIX, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->setAlias(
            self::SERVICE_PLATFORM,
            defined('PHP_WINDOWS_VERSION_BUILD') ? self::SERVICE_PLATFORM_WINDOWS : self::SERVICE_PLATFORM_UNIX
        );
    }
}
