<?php

namespace Meteor\Patch\Task;

use Meteor\IO\IOInterface;
use Meteor\Migrations\Configuration\ConfigurationFactory;
use Meteor\Migrations\MigrationsConstants;
use Meteor\Migrations\Version\VersionFileManager;

class UpdateMigrationVersionFilesHandler
{
    /**
     * @var ConfigurationFactory
     */
    private $configurationFactory;

    /**
     * @var VersionFileManager
     */
    private $versionFileManager;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param ConfigurationFactory $configurationFactory
     * @param VersionFileManager $versionFileManager
     * @param IOInterface $io
     */
    public function __construct(ConfigurationFactory $configurationFactory, VersionFileManager $versionFileManager, IOInterface $io)
    {
        $this->configurationFactory = $configurationFactory;
        $this->versionFileManager = $versionFileManager;
        $this->io = $io;
    }

    /**
     * @param UpdateMigrationVersionFiles $task
     * @param array $config
     *
     * @return bool
     */
    public function handle(UpdateMigrationVersionFiles $task, array $config)
    {
        $migrationConfigs = array();
        if (isset($config['migrations'])) {
            $migrationConfigs[$config['name']] = $config['migrations'];
        }

        if (isset($config['combined'])) {
            foreach ($config['combined'] as $combinedConfig) {
                if (isset($combinedConfig['migrations'])) {
                    $migrationConfigs[$combinedConfig['name']] = $combinedConfig['migrations'];
                }
            }
        }

        if (empty($migrationConfigs)) {
            return;
        }

        $this->io->text('Storing current migration status in the backup');

        foreach ($migrationConfigs as $migrationConfig) {
            $configuration = $this->configurationFactory->createConfiguration(
                MigrationsConstants::TYPE_DATABASE,
                $migrationConfig,
                $task->patchDir,
                $task->installDir
            );

            $this->io->debug(sprintf('Storing current database migration version for "%s"', $migrationConfig['table']));
            $this->versionFileManager->setCurrentVersion(
                $configuration->getCurrentVersion(),
                $task->backupDir,
                $migrationConfig['table'],
                VersionFileManager::DATABASE_MIGRATION
            );

            $configuration = $this->configurationFactory->createConfiguration(
                MigrationsConstants::TYPE_FILE,
                $migrationConfig,
                $task->patchDir,
                $task->installDir
            );

            $this->io->debug(sprintf('Storing current file migration version for "%s"', $migrationConfig['table']));
            $this->versionFileManager->setCurrentVersion(
                $configuration->getCurrentVersion(),
                $task->backupDir,
                $migrationConfig['table'],
                VersionFileManager::FILE_MIGRATION
            );
        }

        $this->io->newLine();
    }
}
