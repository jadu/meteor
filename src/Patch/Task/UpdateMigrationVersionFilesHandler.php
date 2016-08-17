<?php

namespace Meteor\Patch\Task;

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
     * @param ConfigurationFactory $configurationFactory
     * @param VersionFileManager $versionFileManager
     */
    public function __construct(ConfigurationFactory $configurationFactory, VersionFileManager $versionFileManager)
    {
        $this->configurationFactory = $configurationFactory;
        $this->versionFileManager = $versionFileManager;
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

        foreach ($migrationConfigs as $migrationConfig) {
            $configuration = $this->configurationFactory->createConfiguration(
                MigrationsConstants::TYPE_DATABASE,
                $migrationConfig,
                $task->patchDir,
                $task->installDir
            );

            $this->versionFileManager->setCurrentVersion(
                $configuration->getCurrentVersion(),
                $task->installDir,
                $migrationConfig['table'],
                VersionFileManager::DATABASE_MIGRATION
            );

            $configuration = $this->configurationFactory->createConfiguration(
                MigrationsConstants::TYPE_FILE,
                $migrationConfig,
                $task->patchDir,
                $task->installDir
            );

            $this->versionFileManager->setCurrentVersion(
                $configuration->getCurrentVersion(),
                $task->installDir,
                $migrationConfig['table'],
                VersionFileManager::FILE_MIGRATION
            );
        }
    }
}
