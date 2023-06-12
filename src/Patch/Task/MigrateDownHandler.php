<?php

namespace Meteor\Patch\Task;

use Meteor\IO\IOInterface;
use Meteor\Migrations\MigrationsConstants;
use Meteor\Migrations\Migrator;
use Meteor\Migrations\Version\VersionFileManager;

class MigrateDownHandler
{
    /**
     * @var Migrator
     */
    private $migrator;

    /**
     * @var VersionFileManager
     */
    private $versionFileManager;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param Migrator $migrator
     * @param VersionFileManager $versionFileManager
     * @param IOInterface $io
     */
    public function __construct(Migrator $migrator, VersionFileManager $versionFileManager, IOInterface $io)
    {
        $this->migrator = $migrator;
        $this->versionFileManager = $versionFileManager;
        $this->io = $io;
    }

    /**
     * @param MigrateDown $task
     * @param array $config
     */
    public function handle(MigrateDown $task, array $config)
    {
        if (isset($config['migrations'])) {
            if ($this->migrator->validateConfiguration($task->type, $config['migrations'], $task->workingDir)) {
                $result = $this->runMigrations($task->type, $task->backupDir, $task->workingDir, $task->installDir, $task->ignoreUnavailableMigrations, $config);
                if (!$result) {
                    return false;
                }
            }
        }

        if (isset($config['combined'])) {
            // Migrate down in reverse
            $combinedConfigs = array_reverse($config['combined']);
            foreach ($combinedConfigs as $combinedConfig) {
                if (isset($combinedConfig['migrations'])) {
                    if ($this->migrator->validateConfiguration($task->type, $combinedConfig['migrations'], $task->workingDir)) {
                        $result = $this->runMigrations($task->type, $task->backupDir, $task->workingDir, $task->installDir, $task->ignoreUnavailableMigrations, $combinedConfig);
                        if (!$result) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param string $type
     * @param string $backupDir
     * @param string $workingDir
     * @param string $installDir
     * @param bool $ignoreUnavailableMigrations
     * @param array $config
     *
     * @return bool
     */
    private function runMigrations($type, $backupDir, $workingDir, $installDir, $ignoreUnavailableMigrations, array $config)
    {
        $this->io->text(sprintf('Running <info>%s</> %s migrations', $config['name'], $type));

        $versionFile = $type === MigrationsConstants::TYPE_FILE ? VersionFileManager::FILE_MIGRATION : VersionFileManager::DATABASE_MIGRATION;
        $version = $this->versionFileManager->getCurrentVersion(
            $backupDir,
            $config['migrations']['table'],
            $versionFile
        );

        return $this->migrator->migrate(
            $workingDir,
            $installDir,
            $config['migrations'],
            $type,
            $version,
            $ignoreUnavailableMigrations
        );
    }
}
