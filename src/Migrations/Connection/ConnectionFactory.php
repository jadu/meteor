<?php

namespace Meteor\Migrations\Connection;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOMySql\Driver;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use DOMDocument;
use Jadu\Bundle\EncryptionBundle\Encryptor\AesCbcEncryptor;
use Jadu\Bundle\EncryptionBundle\Type\EncryptedTextType;
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
        return new Connection([], new Driver());
    }

    /**
     * @param array $configuration
     * @param $installDir
     * @return Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public function createConnection(array $configuration, $installDir)
    {
        Type::addType('unicodetext', 'Jadu\DoctrineTypes\UnicodeTextType');
        Type::addType(EncryptedTextType::ENCRYPTED_TEXT_TYPE, EncryptedTextType::class);

        $type = Type::getType(EncryptedTextType::ENCRYPTED_TEXT_TYPE);
        if ($type instanceof EncryptedTextType) {
            $type->setEncryptor(new AesCbcEncryptor(
               $this->getEncryptionKey($installDir)
            ));
        }

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

            $this->connection = $this->createConnection($configuration, $installDir);

            // Map enum to string (http://docs.doctrine-project.org/en/latest/cookbook/mysql-enums.html)
            $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

            // Attempt to connect
            $this->connection->connect();
        }

        return $this->connection;
    }

    private function getEncryptionKey($installDir)
    {
        $configFile = $installDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'constants.xml';

        if (file_exists($configFile)) {
            $dom = new DOMDocument();
            $dom->load($configFile);

            $nodes = $dom->getElementsByTagName('encryption_key');

            if ($nodes->length > 0) {
                return trim($nodes->item(0)->textContent);
            }
        }
        return '';
    }
}
