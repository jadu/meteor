<?php

namespace Meteor\Patch\Task;

use Meteor\Migrations\Connection\ConnectionFactory;

class CheckDatabaseConnectionHandler
{
    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @param ConnectionFactory $connectionFactory
     */
    public function __construct(ConnectionFactory $connectionFactory)
    {
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * @param CheckDatabaseConnection $task
     * @param array $config
     */
    public function handle(CheckDatabaseConnection $task, array $config)
    {
        if (!$this->hasMigrations($config)) {
            // Only try to create the connection if there are migrations
            return;
        }

        $this->connectionFactory->getConnection($task->installDir);
    }

    /**
     * @param array $config
     *
     * @return string
     */
    private function hasMigrations(array $config)
    {
        if (isset($config['migrations'])) {
            return true;
        }

        if (isset($config['combined'])) {
            foreach ($config['combined'] as $combinedConfig) {
                if (isset($combinedConfig['migrations'])) {
                    return true;
                }
            }
        }

        return false;
    }
}
