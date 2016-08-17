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
                    $result = $this->runMigrations($task->type, $task->workingDir, $task->installDir, $combinedConfig);
                    if (!$result) {
                        return false;
                    }
                }
            }
        }

        if (isset($config['migrations'])) {
            $result = $this->runMigrations($task->type, $task->workingDir, $task->installDir, $config);
            if (!$result) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $type
     * @param string $workingDir
     * @param string $installDir
     * @param array $config
     *
     * @return bool
     */
    private function runMigrations($type, $workingDir, $installDir, array $config)
    {
        $this->io->text(sprintf('Running <info>%s</> %s migrations', $config['name'], $type));

        return $this->migrator->migrate(
            $workingDir,
            $installDir,
            $config['migrations'],
            $type,
            'latest'
        );
    }
}
