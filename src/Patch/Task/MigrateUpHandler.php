<?php

namespace Meteor\Patch\Task;

use Meteor\IO\IOInterface;
use Meteor\Migrations\Migrator;

class MigrateUpHandler
{
    /**
     * @var Migrator
     */
    private $migrator;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param Migrator $migrator
     * @param IOInterface $io
     */
    public function __construct(Migrator $migrator, IOInterface $io)
    {
        $this->migrator = $migrator;
        $this->io = $io;
    }

    /**
     * @param MigrateUp $task
     * @param array $config
     */
    public function handle(MigrateUp $task, array $config)
    {
        if (isset($config['combined'])) {
            foreach ($config['combined'] as $combinedConfig) {
                if (isset($combinedConfig['migrations'])) {
                    if ($this->migrator->validateConfiguration($task->type, $combinedConfig['migrations'], $task->workingDir)) {
                        $result = $this->runMigrations($task->type, $task->workingDir, $task->installDir, $task->ignoreUnavailableMigrations, $combinedConfig);
                        if (!$result) {
                            return false;
                        }
                    }
                }
            }
        }

        if (isset($config['migrations'])) {
            if ($this->migrator->validateConfiguration($task->type, $config['migrations'], $task->workingDir)) {
                $result = $this->runMigrations($task->type, $task->workingDir, $task->installDir, $task->ignoreUnavailableMigrations, $config);
                if (!$result) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param string $type
     * @param string $workingDir
     * @param string $installDir
     * @param bool $ignoreUnavailableMigrations
     * @param array $config
     *
     * @return bool
     */
    private function runMigrations($type, $workingDir, $installDir, $ignoreUnavailableMigrations, array $config)
    {
        $this->io->text(sprintf('Running <info>%s</> %s migrations', $config['name'], $type));

        return $this->migrator->migrate(
            $workingDir,
            $installDir,
            $config['migrations'],
            $type,
            'latest',
            $ignoreUnavailableMigrations
        );
    }
}
