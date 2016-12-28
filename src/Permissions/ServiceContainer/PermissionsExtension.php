<?php

namespace Meteor\Permissions\ServiceContainer;

use Meteor\Cli\Application;
use Meteor\Cli\ServiceContainer\CliExtension;
use Meteor\Filesystem\ServiceContainer\FilesystemExtension;
use Meteor\IO\ServiceContainer\IOExtension;
use Meteor\Platform\ServiceContainer\PlatformExtension;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class PermissionsExtension extends ExtensionBase implements ExtensionInterface
{
    const SERVICE_COMMAND_RESET_PERMISSIONS = 'permissions.cli.command.reset_permissions';
    const SERVICE_PERMISSION_LOADER = 'permissions.permission_loader';
    const SERVICE_PERMISSION_SETTER = 'permissions.permission_setter';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'permissions';
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
        $this->loadPermissionLoader($container);
        $this->loadPermissionSetter($container);
        $this->loadResetPermissionsCommand($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadPermissionLoader(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_PERMISSION_LOADER, new Definition('Meteor\Permissions\PermissionLoader'));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadPermissionSetter(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_PERMISSION_SETTER, new Definition('Meteor\Permissions\PermissionSetter', [
            new Reference(PlatformExtension::SERVICE_PLATFORM),
            new Reference(self::SERVICE_PERMISSION_LOADER),
            new Reference(IOExtension::SERVICE_IO),
        ]));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadResetPermissionsCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Permissions\Cli\Command\ResetPermissionsCommand', [
            null,
            '%' . Application::PARAMETER_CONFIG . '%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(PlatformExtension::SERVICE_PLATFORM),
            new Reference(FilesystemExtension::SERVICE_FILESYSTEM),
            new Reference(self::SERVICE_PERMISSION_SETTER),
        ]);
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_RESET_PERMISSIONS, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
