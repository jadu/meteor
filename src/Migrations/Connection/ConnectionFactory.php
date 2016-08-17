<?php

namespace Meteor\Migrations\Connection;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOMySql\Driver;
use Doctrine\DBAL\DriverManager;
use Meteor\Migrations\Connection\Configuration\Loader\ConfigurationLoaderInterface;
use Meteor\Migrations\Connection\Platform\SQLServer2008Platform;

class ConnectionFactory
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ConfigurationLoaderInterface
     */
    private $configurationLoader;

    /**
     * @param ConfigurationLoaderInterface $configurationLoader
     */
    public function __construct(ConfigurationLoaderInterface $configurationLoader)
    {
        $this->configurationLoader = $configurationLoader;
    }

    /**
     * Creates a fake connection so the migration generate command can
     * run without needing to know DB credentials.
     *
     * @return Connection
     */
    public function createFakeConnection()
    {
        return new Connection(array(), new Driver());
    }

    /**
     * @param array $configuration
     */
    public function createConnection(array $configuration)
    {
        return DriverManager::getConnection($configuration);
    }

    /**
     * @param string $installDir
     *
     * @return Connection
     */
    public function getConnection($installDir)
    {
        if ($this->connection === null) {
            $configuration = $this->configurationLoader->load($installDir);
            if ($configuration['driver'] === 'pdo_sqlsrv' || $configuration['driver'] === 'sqlsrv') {
                // Use an extended Platform class for SQL Server to fix a few issues
                $configuration['platform'] = new SQLServer2008Platform();
            }

            $this->connection = $this->createConnection($configuration);

            // Map enum to string (http://docs.doctrine-project.org/en/latest/cookbook/mysql-enums.html)
            $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

            // Attempt to connect
            $this->connection->connect();
        }

        return $this->connection;
    }
}
