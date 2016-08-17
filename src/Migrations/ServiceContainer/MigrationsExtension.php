<?php

namespace Meteor\Migrations\ServiceContainer;

use Meteor\Cli\Application;
use Meteor\Cli\ServiceContainer\CliExtension;
use Meteor\Filesystem\ServiceContainer\FilesystemExtension;
use Meteor\IO\ServiceContainer\IOExtension;
use Meteor\Logger\ServiceContainer\LoggerExtension;
use Meteor\Migrations\MigrationsConstants;
use Meteor\Platform\ServiceContainer\PlatformExtension;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class MigrationsExtension implements ExtensionInterface
{
    const SERVICE_CONFIGURATION_FACTORY = 'migrations.configuration.factory';
    const SERVICE_CONNECTION_CONFIGURATION_LOADER = 'migrations.connection.configuration.loader';
    const SERVICE_CONNECTION_CONFIGURATION_LOADER_INPUT_OPTION = 'migrations.connection.configuration.loader.input_option';
    const SERVICE_CONNECTION_CONFIGURATION_LOADER_INPUT_QUESTION = 'migrations.connection.configuration.loader.input_question';
    const SERVICE_CONNECTION_CONFIGURATION_LOADER_SYSTEM = 'migrations.connection.configuration.loader.system';
    const SERVICE_CONNECTION_FACTORY = 'migrations.connection.factory';
    const SERVICE_COMMAND_EXECUTE_DATABASE_MIGRATION = 'migrations.cli.command.execute_database_migration';
    const SERVICE_COMMAND_EXECUTE_FILE_MIGRATION = 'migrations.cli.command.execute_file_migration';
    const SERVICE_COMMAND_GENERATE_DATABASE_MIGRATION = 'migrations.cli.command.generate_database_migration';
    const SERVICE_COMMAND_GENERATE_FILE_MIGRATION = 'migrations.cli.command.generate_file_migration';
    const SERVICE_COMMAND_MIGRATE_DATABASE = 'migrations.cli.command.migrate_database';
    const SERVICE_COMMAND_MIGRATE_FILES = 'migrations.cli.command.migrate_files';
    const SERVICE_COMMAND_DATABASE_MIGRATION_STATUS = 'migrations.cli.command.database_status';
    const SERVICE_COMMAND_FILE_MIGRATION_STATUS = 'migrations.cli.command.file_status';
    const SERVICE_COMMAND_DATABASE_MIGRATION_VERSION = 'migrations.cli.command.database_version';
    const SERVICE_COMMAND_FILE_MIGRATION_VERSION = 'migrations.cli.command.file_version';
    const SERVICE_MIGRATION_GENERATOR = 'migrations.migration_generator';
    const SERVICE_MIGRATOR = 'migrations.migrator';
    const SERVICE_STATUS_OUTPUTTER = 'migrations.outputter.status';
    const SERVICE_VERSION_FILE_MANAGER = 'migrations.version.version_file_manager';
    const SERVICE_VERSION_FILE_MIGRATION_VERSION_STORAGE_FACTORY = 'migrations.version.file_migration_version_storage_factory';
    const SERVICE_VERSION_MANAGER = 'migrations.version.manager';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'migrations';
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
            ->children()
                ->scalarNode('table')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('name')
                    ->defaultValue('Migrations')
                ->end()
                ->scalarNode('namespace')
                    ->defaultValue('DoctrineMigrations')
                ->end()
                ->scalarNode('directory')
                    ->defaultValue('upgrades/migrations')
                ->end()
            ->end()
        ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadConnectionConfigurationLoader($container);
        $this->loadConnectionFactory($container);
        $this->loadConfigurationFactory($container);
        $this->loadMigrationGenerator($container);
        $this->loadMigrator($container);
        $this->loadExecuteDatabaseMigrationCommand($container);
        $this->loadExecuteFileMigrationCommand($container);
        $this->loadGenerateDatabaseMigrationCommand($container);
        $this->loadGenerateFileMigrationCommand($container);
        $this->loadMigrateDatabaseCommand($container);
        $this->loadMigrateFilesCommand($container);
        $this->loadDatabaseMigrationStatusCommand($container);
        $this->loadFileMigrationStatusCommand($container);
        $this->loadDatabaseMigrationVersionCommand($container);
        $this->loadFileMigrationVersionCommand($container);
        $this->loadStatusOutputter($container);
        $this->loadVersionFileManager($container);
        $this->loadVersionFileMigrationVersionStorageFactory($container);
        $this->loadVersionManager($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadConnectionConfigurationLoader(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_CONNECTION_CONFIGURATION_LOADER_INPUT_OPTION, new Definition('Meteor\Migrations\Connection\Configuration\Loader\InputOptionConfigurationLoader', array(
            new Reference(IOExtension::SERVICE_IO),
        )));
        $container->setDefinition(self::SERVICE_CONNECTION_CONFIGURATION_LOADER_SYSTEM, new Definition('Meteor\Migrations\Connection\Configuration\Loader\SystemConfigurationLoader'));
        $container->setDefinition(self::SERVICE_CONNECTION_CONFIGURATION_LOADER_INPUT_QUESTION, new Definition('Meteor\Migrations\Connection\Configuration\Loader\InputQuestionConfigurationLoader', array(
            new Reference(IOExtension::SERVICE_IO),
        )));

        $container->setDefinition(self::SERVICE_CONNECTION_CONFIGURATION_LOADER, new Definition('Meteor\Migrations\Connection\Configuration\Loader\ChainedConfigurationLoader', array(
            array(
                new Reference(self::SERVICE_CONNECTION_CONFIGURATION_LOADER_INPUT_OPTION),
                new Reference(self::SERVICE_CONNECTION_CONFIGURATION_LOADER_SYSTEM),
                new Reference(self::SERVICE_CONNECTION_CONFIGURATION_LOADER_INPUT_QUESTION),
            ),
        )));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadConnectionFactory(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_CONNECTION_FACTORY, new Definition('Meteor\Migrations\Connection\ConnectionFactory', array(
            new Reference(self::SERVICE_CONNECTION_CONFIGURATION_LOADER),
        )));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadConfigurationFactory(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_CONFIGURATION_FACTORY, new Definition('Meteor\Migrations\Configuration\ConfigurationFactory', array(
            new Reference(self::SERVICE_CONNECTION_FACTORY),
            new Reference(self::SERVICE_VERSION_FILE_MIGRATION_VERSION_STORAGE_FACTORY),
            new Reference(self::SERVICE_VERSION_FILE_MANAGER),
            new Reference(IOExtension::SERVICE_IO),
        )));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadExecuteDatabaseMigrationCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Migrations\Cli\Command\ExecuteMigrationCommand', array(
            'migrations:execute',
            '%'.Application::PARAMETER_CONFIG.'%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(PlatformExtension::SERVICE_PLATFORM),
            new Reference(self::SERVICE_MIGRATOR),
            new Reference(LoggerExtension::SERVICE_LOGGER),
            MigrationsConstants::TYPE_DATABASE,
        ));
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_EXECUTE_DATABASE_MIGRATION, $definition);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadExecuteFileMigrationCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Migrations\Cli\Command\ExecuteMigrationCommand', array(
            'file-migrations:execute',
            '%'.Application::PARAMETER_CONFIG.'%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(PlatformExtension::SERVICE_PLATFORM),
            new Reference(self::SERVICE_MIGRATOR),
            new Reference(LoggerExtension::SERVICE_LOGGER),
            MigrationsConstants::TYPE_FILE,
        ));
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_EXECUTE_FILE_MIGRATION, $definition);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadGenerateDatabaseMigrationCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Migrations\Cli\Command\GenerateMigrationCommand', array(
            'migrations:generate',
            '%'.Application::PARAMETER_CONFIG.'%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(self::SERVICE_MIGRATION_GENERATOR),
            MigrationsConstants::TYPE_DATABASE,
        ));
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_GENERATE_DATABASE_MIGRATION, $definition);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadGenerateFileMigrationCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Migrations\Cli\Command\GenerateMigrationCommand', array(
            'file-migrations:generate',
            '%'.Application::PARAMETER_CONFIG.'%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(self::SERVICE_MIGRATION_GENERATOR),
            MigrationsConstants::TYPE_FILE,
        ));
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_GENERATE_FILE_MIGRATION, $definition);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadMigrationGenerator(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_MIGRATION_GENERATOR, new Definition('Meteor\Migrations\Generator\MigrationGenerator'));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadMigrator(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_MIGRATOR, new Definition('Meteor\Migrations\Migrator', array(
            new Reference(self::SERVICE_CONFIGURATION_FACTORY),
            new Reference(IOExtension::SERVICE_IO),
        )));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadMigrateDatabaseCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Migrations\Cli\Command\MigrateCommand', array(
            'migrations:migrate',
            '%'.Application::PARAMETER_CONFIG.'%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(PlatformExtension::SERVICE_PLATFORM),
            new Reference(self::SERVICE_MIGRATOR),
            new Reference(LoggerExtension::SERVICE_LOGGER),
            MigrationsConstants::TYPE_DATABASE,
        ));
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_MIGRATE_DATABASE, $definition);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadMigrateFilesCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Migrations\Cli\Command\MigrateCommand', array(
            'file-migrations:migrate',
            '%'.Application::PARAMETER_CONFIG.'%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(PlatformExtension::SERVICE_PLATFORM),
            new Reference(self::SERVICE_MIGRATOR),
            new Reference(LoggerExtension::SERVICE_LOGGER),
            MigrationsConstants::TYPE_FILE,
        ));
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_MIGRATE_FILES, $definition);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadDatabaseMigrationStatusCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Migrations\Cli\Command\StatusCommand', array(
            'migrations:status',
            '%'.Application::PARAMETER_CONFIG.'%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(PlatformExtension::SERVICE_PLATFORM),
            new Reference(self::SERVICE_STATUS_OUTPUTTER),
            MigrationsConstants::TYPE_DATABASE,
        ));
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_DATABASE_MIGRATION_STATUS, $definition);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadFileMigrationStatusCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Migrations\Cli\Command\StatusCommand', array(
            'file-migrations:status',
            '%'.Application::PARAMETER_CONFIG.'%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(PlatformExtension::SERVICE_PLATFORM),
            new Reference(self::SERVICE_STATUS_OUTPUTTER),
            MigrationsConstants::TYPE_FILE,
        ));
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_FILE_MIGRATION_STATUS, $definition);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadStatusOutputter(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_STATUS_OUTPUTTER, new Definition('Meteor\Migrations\Outputter\StatusOutputter', array(
            new Reference(self::SERVICE_CONFIGURATION_FACTORY),
            new Reference(IOExtension::SERVICE_IO),
        )));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadDatabaseMigrationVersionCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Migrations\Cli\Command\VersionCommand', array(
            'migrations:version',
            '%'.Application::PARAMETER_CONFIG.'%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(PlatformExtension::SERVICE_PLATFORM),
            new Reference(self::SERVICE_VERSION_MANAGER),
            MigrationsConstants::TYPE_DATABASE,
        ));
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_DATABASE_MIGRATION_VERSION, $definition);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadFileMigrationVersionCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Migrations\Cli\Command\VersionCommand', array(
            'file-migrations:version',
            '%'.Application::PARAMETER_CONFIG.'%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(PlatformExtension::SERVICE_PLATFORM),
            new Reference(self::SERVICE_VERSION_MANAGER),
            MigrationsConstants::TYPE_FILE,
        ));
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_FILE_MIGRATION_VERSION, $definition);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadVersionFileManager(ContainerBuilder $container)
    {
        $container->setDefinition(
            self::SERVICE_VERSION_FILE_MANAGER,
            new Definition('Meteor\Migrations\Version\VersionFileManager')
        );
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadVersionFileMigrationVersionStorageFactory(ContainerBuilder $container)
    {
        $container->setDefinition(
            self::SERVICE_VERSION_FILE_MIGRATION_VERSION_STORAGE_FACTORY,
            new Definition('Meteor\Migrations\Version\FileMigrationVersionStorageFactory', array(
                new Reference(FilesystemExtension::SERVICE_FILESYSTEM),
            ))
        );
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadVersionManager(ContainerBuilder $container)
    {
        $container->setDefinition(
            self::SERVICE_VERSION_MANAGER,
            new Definition('Meteor\Migrations\Version\VersionManager', array(
                new Reference(self::SERVICE_CONFIGURATION_FACTORY),
                new Reference(IOExtension::SERVICE_IO),
            ))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
