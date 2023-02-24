<?php

namespace Meteor\Migrations\Configuration;

use Doctrine\Migrations\Finder\GlobFinder;
use Doctrine\Migrations\OutputWriter;
use InvalidArgumentException;
use Meteor\IO\IOInterface;
use Meteor\Migrations\Connection\ConnectionFactory;
use Meteor\Migrations\MigrationsConstants;
use Meteor\Migrations\Version\FileMigrationVersionStorageFactory;
use Meteor\Migrations\Version\VersionFileManager;

class ConfigurationFactory
{
    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @var FileMigrationVersionStorageFactory
     */
    private $fileMigrationVersionStorageFactory;

    /**
     * @var VersionFileManager
     */
    private $versionFileManager;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param ConnectionFactory $connectionFactory
     * @param FileMigrationVersionStorageFactory $fileMigrationVersionStorageFactory
     * @param VersionFileManager $versionFileManager
     * @param IOInterface $io
     */
    public function __construct(
        ConnectionFactory $connectionFactory,
        FileMigrationVersionStorageFactory $fileMigrationVersionStorageFactory,
        VersionFileManager $versionFileManager,
        IOInterface $io
    ) {
        $this->connectionFactory = $connectionFactory;
        $this->fileMigrationVersionStorageFactory = $fileMigrationVersionStorageFactory;
        $this->versionFileManager = $versionFileManager;
        $this->io = $io;
    }

    /**
     * @param string $type
     * @param array $config
     * @param string $patchDir
     * @param string $installDir
     *
     * @return AbstractConfiguration
     */
    public function createConfiguration($type, array $config, $patchDir, $installDir)
    {
        if ($type === MigrationsConstants::TYPE_FILE) {
            return $this->createFileConfiguration($config, $patchDir, $installDir);
        }

        if ($type === MigrationsConstants::TYPE_DATABASE) {
            return $this->createDatabaseConfiguration($config, $patchDir, $installDir);
        }

        throw new InvalidArgumentException('Invalid migration type');
    }

    /**
     * @param array $config
     * @param string $patchDir
     * @param string $installDir
     *
     * @return DatabaseConfiguration
     */
    public function createDatabaseConfiguration(array $config, $patchDir, $installDir)
    {
        $configuration = $this->create(DatabaseConfiguration::class, $config, $patchDir, $installDir);

        // NB: This will attempt to connect to the database
        $versions = $configuration->registerMigrationsFromDirectory($patchDir . '/' . $config['directory']);
        $configuration->setVersions($versions);

        return $configuration;
    }

    /**
     * @param array $config
     * @param string $patchDir
     * @param string $installDir
     *
     * @return FileConfiguration
     */
    public function createFileConfiguration(array $config, $patchDir, $installDir)
    {
        $config['directory'] = $config['directory'] . '/' . FileConfiguration::MIGRATION_DIRECTORY;
        $configuration = $this->create(FileConfiguration::class, $config, $patchDir, $installDir);

        $versionStorage = $this->fileMigrationVersionStorageFactory->create($installDir, $config['table']);
        $configuration->setVersionStorage($versionStorage);

        // NB: This will attempt to connect to the database
        $versions = $configuration->registerMigrationsFromDirectory($patchDir . '/' . $config['directory']);
        $configuration->setVersions($versions);
        if (!$versionStorage->isInitialised()) {
            // The version storage file does not exist yet, create it
            $versionStorage->initialise();
        }

        return $configuration;
    }

    /**
     * @param string $className
     * @param array $config
     * @param string $patchDir
     * @param string $installDir
     *
     * @return mixed
     */
    private function create($className, array $config, $patchDir, $installDir)
    {
        $connection = $this->connectionFactory->getConnection($installDir);

        $configuration = new $className($connection, $this->createOutputWriter(), new GlobFinder());
        $configuration->setName($config['name']);
        $configuration->setMigrationsNamespace($config['namespace']);
        $configuration->setMigrationsTableName($config['table']);
        $configuration->setMigrationsDirectory($patchDir . '/' . $config['directory']);

        // Set the install dir for use within migrations
        $configuration->setJaduPath($installDir);

        return $configuration;
    }

    /**
     * @return OutputWriter
     */
    private function createOutputWriter()
    {
        $io = $this->io;

        return new OutputWriter(function ($message) use ($io) {
            return $io->writeln($message);
        });
    }
}
