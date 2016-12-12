<?php

namespace Meteor\Patch\ServiceContainer;

use Exception;
use InvalidArgumentException;
use Meteor\Cli\Application;
use Meteor\Cli\ServiceContainer\CliExtension;
use Meteor\Configuration\ServiceContainer\ConfigurationExtension;
use Meteor\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Meteor\Filesystem\ServiceContainer\FilesystemExtension;
use Meteor\IO\ServiceContainer\IOExtension;
use Meteor\Logger\ServiceContainer\LoggerExtension;
use Meteor\Migrations\ServiceContainer\MigrationsExtension;
use Meteor\Patch\Event\PatchEvents;
use Meteor\Patch\Strategy\Overwrite\ServiceContainer\OverwritePatchStrategyExtension;
use Meteor\Permissions\ServiceContainer\PermissionsExtension;
use Meteor\Platform\ServiceContainer\PlatformExtension;
use Meteor\Scripts\ScriptEventProviderInterface;
use Meteor\Scripts\ServiceContainer\ScriptsExtension;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class PatchExtension extends ExtensionBase implements ExtensionInterface, ScriptEventProviderInterface
{
    const PARAMETER_STRATEGY = 'patch.strategy';
    const SERVICE_BACKUP_FINDER = 'patch.backup.finder';
    const SERVICE_COMMAND_APPLY = 'patch.cli.command.apply';
    const SERVICE_COMMAND_VERIFY = 'patch.cli.command.verify';
    const SERVICE_COMMAND_CLEAR_LOCK = 'patch.cli.command.clear_lock';
    const SERVICE_COMMAND_ROLLBACK = 'patch.cli.command.rollback';
    const SERVICE_COMMAND_VERSION_INFO = 'patch.cli.command.version_info';
    const SERVICE_LOCKER = 'patch.locker';
    const SERVICE_MANIFEST_CHECKER = 'patch.manifest_checker';
    const SERVICE_STRATEGY_PREFIX = 'patch.strategy';
    const SERVICE_STRATEGY = 'patch.strategy';
    const SERVICE_TASK_BUS = 'patch.task_bus';
    const SERVICE_TASK_BACKUP_FILES_HANDLER = 'patch.task.backup_files_handler';
    const SERVICE_TASK_CHECK_DATABASE_CONNECTION_HANDLER = 'patch.task.check_database_connection_handler';
    const SERVICE_TASK_CHECK_DISK_SPACE_HANDLER = 'patch.task.check_disk_space_handler';
    const SERVICE_TASK_CHECK_MODULE_CMS_DEPENDENCY_HANDLER = 'patch.task.check_module_cms_dependency_handler';
    const SERVICE_TASK_CHECK_VERSION_HANDLER = 'patch.task.check_version_handler';
    const SERVICE_TASK_CHECK_WRITE_PERMISSION_HANDLER = 'patch.task.check_write_permission_handler';
    const SERVICE_TASK_COPY_FILES_HANDLER = 'patch.task.copy_files_handler';
    const SERVICE_TASK_DELETE_BACKUP_HANDLER = 'patch.task.delete_backup_handler';
    const SERVICE_TASK_DISPLAY_VERSION_INFO_HANDLER = 'patch.task.display_version_info_handler';
    const SERVICE_TASK_MIGRATE_DOWN_HANDLER = 'patch.task.migrate_down_handler';
    const SERVICE_TASK_MIGRATE_UP_HANDLER = 'patch.task.migrate_up_handler';
    const SERVICE_TASK_UPDATE_MIGRATION_VERSION_FILES_HANDLER = 'patch.task.update_database_migration_version_files_handler';
    const SERVICE_VERSION_COMPARER = 'patch.version.comparer';
    const TAG_TASK_HANDLER = 'patch.task_handler';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'patch';
    }

    /**
     * {@inheritdoc}
     */
    public function getEventNames()
    {
        return [
            PatchEvents::PRE_APPLY,
            PatchEvents::POST_APPLY,
            PatchEvents::PRE_ROLLBACK,
            PatchEvents::POST_ROLLBACK,
        ];
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
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('strategy')
                    ->defaultValue(OverwritePatchStrategyExtension::STRATEGY_NAME)
                ->end()
                ->scalarNode('includeHiddenFiles')
                    // NB: Unused config parameter but added for backwards compatibility with old Meteor configs
                ->end()
            ->end()
        ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $container->setParameter(self::PARAMETER_STRATEGY, $config['strategy']);

        $this->loadBackupFinder($container);
        $this->loadBackupFilesTaskHandler($container);
        $this->loadCheckDatabaseConnectionTaskHandler($container);
        $this->loadCheckDiskSpaceHandler($container);
        $this->loadCheckModuleCmsDependencyTaskHandler($container);
        $this->loadCheckVersionTaskHandler($container);
        $this->loadCheckWritePermissionTaskHandler($container);
        $this->loadCopyFilesHandler($container);
        $this->loadDeleteBackupHandler($container);
        $this->loadDisplayVersionInfoTaskHandler($container);
        $this->loadMigrateDownTaskHandler($container);
        $this->loadMigrateUpTaskHandler($container);
        $this->loadUpdateMigrationVersionFilesTaskHandler($container);
        $this->loadTaskBus($container);
        $this->loadApplyCommand($container);
        $this->loadVerifyCommand($container);
        $this->loadClearLockCommand($container);
        $this->loadRollbackCommand($container);
        $this->loadVersionInfoCommand($container);
        $this->loadVersionComparer($container);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadBackupFinder(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_BACKUP_FINDER, new Definition('Meteor\Patch\Backup\BackupFinder', [
            new Reference(self::SERVICE_VERSION_COMPARER),
            new Reference(ConfigurationExtension::SERVICE_LOADER),
        ]));
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadTaskBus(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_TASK_BUS, new Definition('Meteor\Patch\Task\TaskBus'));
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadBackupFilesTaskHandler(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Patch\Task\BackupFilesHandler', [
            new Reference(FilesystemExtension::SERVICE_FILESYSTEM),
            new Reference(ConfigurationExtension::SERVICE_LOADER),
            new Reference(IOExtension::SERVICE_IO),
        ]);
        $definition->addTag(self::TAG_TASK_HANDLER, [
            'task' => 'Meteor\Patch\Task\BackupFiles',
        ]);

        $container->setDefinition(self::SERVICE_TASK_BACKUP_FILES_HANDLER, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadCheckDatabaseConnectionTaskHandler(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Patch\Task\CheckDatabaseConnectionHandler', [
            new Reference(MigrationsExtension::SERVICE_CONNECTION_FACTORY),
        ]);
        $definition->addTag(self::TAG_TASK_HANDLER, [
            'task' => 'Meteor\Patch\Task\CheckDatabaseConnection',
        ]);

        $container->setDefinition(self::SERVICE_TASK_CHECK_DATABASE_CONNECTION_HANDLER, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadCheckDiskSpaceHandler(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Patch\Task\CheckDiskSpaceHandler', [
            new Reference(self::SERVICE_BACKUP_FINDER),
            new Reference(FilesystemExtension::SERVICE_FILESYSTEM),
            new Reference(IOExtension::SERVICE_IO),
        ]);
        $definition->addTag(self::TAG_TASK_HANDLER, [
            'task' => 'Meteor\Patch\Task\CheckDiskSpace',
        ]);

        $container->setDefinition(self::SERVICE_TASK_CHECK_DISK_SPACE_HANDLER, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadCheckModuleCmsDependencyTaskHandler(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Patch\Task\CheckModuleCmsDependencyHandler', [
            new Reference(IOExtension::SERVICE_IO),
        ]);
        $definition->addTag(self::TAG_TASK_HANDLER, [
            'task' => 'Meteor\Patch\Task\CheckModuleCmsDependency',
        ]);

        $container->setDefinition(self::SERVICE_TASK_CHECK_MODULE_CMS_DEPENDENCY_HANDLER, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadCheckVersionTaskHandler(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Patch\Task\CheckVersionHandler', [
            new Reference(IOExtension::SERVICE_IO),
            new Reference(self::SERVICE_VERSION_COMPARER),
        ]);
        $definition->addTag(self::TAG_TASK_HANDLER, [
            'task' => 'Meteor\Patch\Task\CheckVersion',
        ]);

        $container->setDefinition(self::SERVICE_TASK_CHECK_VERSION_HANDLER, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadCheckWritePermissionTaskHandler(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Patch\Task\CheckWritePermissionHandler', [
            new Reference(IOExtension::SERVICE_IO),
        ]);
        $definition->addTag(self::TAG_TASK_HANDLER, [
            'task' => 'Meteor\Patch\Task\CheckWritePermission',
        ]);

        $container->setDefinition(self::SERVICE_TASK_CHECK_WRITE_PERMISSION_HANDLER, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadCopyFilesHandler(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Patch\Task\CopyFilesHandler', [
            new Reference(IOExtension::SERVICE_IO),
            new Reference(FilesystemExtension::SERVICE_FILESYSTEM),
            new Reference(PermissionsExtension::SERVICE_PERMISSION_SETTER),
        ]);
        $definition->addTag(self::TAG_TASK_HANDLER, [
            'task' => 'Meteor\Patch\Task\CopyFiles',
        ]);

        $container->setDefinition(self::SERVICE_TASK_COPY_FILES_HANDLER, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadDeleteBackupHandler(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Patch\Task\DeleteBackupHandler', [
            new Reference(IOExtension::SERVICE_IO),
            new Reference(FilesystemExtension::SERVICE_FILESYSTEM),
        ]);
        $definition->addTag(self::TAG_TASK_HANDLER, [
            'task' => 'Meteor\Patch\Task\DeleteBackup',
        ]);

        $container->setDefinition(self::SERVICE_TASK_DELETE_BACKUP_HANDLER, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadDisplayVersionInfoTaskHandler(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Patch\Task\DisplayVersionInfoHandler', [
            new Reference(IOExtension::SERVICE_IO),
            new Reference(self::SERVICE_VERSION_COMPARER),
        ]);
        $definition->addTag(self::TAG_TASK_HANDLER, [
            'task' => 'Meteor\Patch\Task\DisplayVersionInfo',
        ]);

        $container->setDefinition(self::SERVICE_TASK_DISPLAY_VERSION_INFO_HANDLER, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadMigrateDownTaskHandler(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Patch\Task\MigrateDownHandler', [
            new Reference(MigrationsExtension::SERVICE_MIGRATOR),
            new Reference(MigrationsExtension::SERVICE_VERSION_FILE_MANAGER),
            new Reference(IOExtension::SERVICE_IO),
        ]);
        $definition->addTag(self::TAG_TASK_HANDLER, [
            'task' => 'Meteor\Patch\Task\MigrateDown',
        ]);

        $container->setDefinition(self::SERVICE_TASK_MIGRATE_DOWN_HANDLER, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadMigrateUpTaskHandler(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Patch\Task\MigrateUpHandler', [
            new Reference(MigrationsExtension::SERVICE_MIGRATOR),
            new Reference(IOExtension::SERVICE_IO),
        ]);
        $definition->addTag(self::TAG_TASK_HANDLER, [
            'task' => 'Meteor\Patch\Task\MigrateUp',
        ]);

        $container->setDefinition(self::SERVICE_TASK_MIGRATE_UP_HANDLER, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadUpdateMigrationVersionFilesTaskHandler(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Patch\Task\UpdateMigrationVersionFilesHandler', [
            new Reference(MigrationsExtension::SERVICE_CONFIGURATION_FACTORY),
            new Reference(MigrationsExtension::SERVICE_VERSION_FILE_MANAGER),
            new Reference(IOExtension::SERVICE_IO),
        ]);
        $definition->addTag(self::TAG_TASK_HANDLER, [
            'task' => 'Meteor\Patch\Task\UpdateMigrationVersionFiles',
        ]);

        $container->setDefinition(self::SERVICE_TASK_UPDATE_MIGRATION_VERSION_FILES_HANDLER, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadApplyCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Patch\Cli\Command\ApplyCommand', [
            null,
            '%'.Application::PARAMETER_CONFIG.'%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(PlatformExtension::SERVICE_PLATFORM),
            new Reference(self::SERVICE_TASK_BUS),
            new Reference(self::SERVICE_STRATEGY),
            new Reference(self::SERVICE_LOCKER),
            new Reference(self::SERVICE_MANIFEST_CHECKER),
            new Reference(EventDispatcherExtension::SERVICE_EVENT_DISPATCHER),
            new Reference(ScriptsExtension::SERVICE_SCRIPT_RUNNER),
            new Reference(LoggerExtension::SERVICE_LOGGER),
        ]);
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_APPLY, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadVerifyCommand(ContainerBuilder $container)
    {
        $this->loadManifestChecker($container);

        $definition = new Definition('Meteor\Patch\Cli\Command\VerifyCommand', [
            null,
            '%'.Application::PARAMETER_CONFIG.'%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(PlatformExtension::SERVICE_PLATFORM),
            new Reference(self::SERVICE_MANIFEST_CHECKER),
        ]);
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_VERIFY, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadManifestChecker(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_MANIFEST_CHECKER, new Definition('Meteor\Patch\Manifest\ManifestChecker', [
            new Reference(IOExtension::SERVICE_IO),
        ]));
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadClearLockCommand(ContainerBuilder $container)
    {
        $this->loadLocker($container);

        $definition = new Definition('Meteor\Patch\Cli\Command\ClearLockCommand', [
            null,
            '%'.Application::PARAMETER_CONFIG.'%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(PlatformExtension::SERVICE_PLATFORM),
            new Reference(self::SERVICE_LOCKER),
        ]);
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_CLEAR_LOCK, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadLocker(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_LOCKER, new Definition('Meteor\Patch\Lock\Locker'));
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadRollbackCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Patch\Cli\Command\RollbackCommand', [
            null,
            '%'.Application::PARAMETER_CONFIG.'%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(PlatformExtension::SERVICE_PLATFORM),
            new Reference(self::SERVICE_VERSION_COMPARER),
            new Reference(self::SERVICE_BACKUP_FINDER),
            new Reference(self::SERVICE_TASK_BUS),
            new Reference(self::SERVICE_STRATEGY),
            new Reference(self::SERVICE_LOCKER),
            new Reference(EventDispatcherExtension::SERVICE_EVENT_DISPATCHER),
            new Reference(ScriptsExtension::SERVICE_SCRIPT_RUNNER),
            new Reference(LoggerExtension::SERVICE_LOGGER),
        ]);
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_ROLLBACK, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadVersionInfoCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Patch\Cli\Command\VersionInfoCommand', [
            null,
            '%'.Application::PARAMETER_CONFIG.'%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(PlatformExtension::SERVICE_PLATFORM),
            new Reference(self::SERVICE_TASK_BUS),
        ]);
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_VERSION_INFO, $definition);
    }

    /**
     * @param ContailerBuilder $container
     */
    private function loadVersionComparer(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_VERSION_COMPARER, new Definition('Meteor\Patch\Version\VersionComparer'));
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $strategyName = $container->getParameter(self::PARAMETER_STRATEGY);
        $strategyServiceId = self::SERVICE_STRATEGY_PREFIX.'.'.$strategyName;

        if (!$container->has($strategyServiceId)) {
            throw new InvalidArgumentException(sprintf('Unable to find patch strategy `%s`.', $strategyName));
        }

        $container->setAlias(self::SERVICE_STRATEGY, $strategyServiceId);

        foreach ($container->findTaggedServiceIds(self::TAG_TASK_HANDLER) as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['task'])) {
                    throw new Exception(sprintf('The %s tag must always have a task attribute', self::TAG_TASK_HANDLER));
                }

                $container->getDefinition(self::SERVICE_TASK_BUS)->addMethodCall('registerHandler', [
                    $attributes['task'],
                    new Reference($id),
                ]);
            }
        }
    }
}
