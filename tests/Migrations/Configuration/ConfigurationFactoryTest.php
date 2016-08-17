<?php

namespace Meteor\Migrations\Configuration;

use Meteor\IO\NullIO;
use Meteor\Migrations\Version\VersionFileManager;
use Mockery;

class ConfigurationFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $connectionFactory;
    private $fileMigrationVersionStorageFactory;
    private $versionFileManager;
    private $io;
    private $configurationFactory;

    public function setUp()
    {
        $this->connectionFactory = Mockery::mock('Meteor\Migrations\Connection\ConnectionFactory');
        $this->fileMigrationVersionStorageFactory = Mockery::mock('Meteor\Migrations\Version\FileMigrationVersionStorageFactory');
        $this->versionFileManager = Mockery::mock('Meteor\Migrations\Version\VersionFileManager');
        $this->io = new NullIO();

        $this->configurationFactory = new ConfigurationFactory(
            $this->connectionFactory,
            $this->fileMigrationVersionStorageFactory,
            $this->versionFileManager,
            $this->io
        );
    }

    public function testCreateDatabaseConfiguration()
    {
        $connection = Mockery::mock('Doctrine\DBAL\Connection');
        $this->connectionFactory->shouldReceive('getConnection')
            ->with('/path/to/install')
            ->andReturn($connection)
            ->once();

        $configuration = $this->configurationFactory->createDatabaseConfiguration(
            array(
                'name' => 'jadu/xfp',
                'namespace' => 'Migrations',
                'table' => 'JaduMigrationsXFP',
                'directory' => 'upgrades',
            ),
            '/path/to/patch',
            '/path/to/install',
            true
        );

        $this->assertSame($connection, $configuration->getConnection());
        $this->assertSame('jadu/xfp', $configuration->getName());
        $this->assertSame('Migrations', $configuration->getMigrationsNamespace());
        $this->assertSame('JaduMigrationsXFP', $configuration->getMigrationsTableName());
        $this->assertSame('/path/to/patch/upgrades', $configuration->getMigrationsDirectory());
        $this->assertSame('/path/to/install', $configuration->getJaduPath());
    }

    public function testCreateFileConfiguration()
    {
        $connection = Mockery::mock('Doctrine\DBAL\Connection');
        $this->connectionFactory->shouldReceive('getConnection')
            ->with('/path/to/install')
            ->andReturn($connection)
            ->once();

        $fileMigrationVersionStorage = Mockery::mock('Meteor\Migrations\Version\FileMigrationVersionStorage', array(
            'isInitialised' => true,
        ));
        $this->fileMigrationVersionStorageFactory->shouldReceive('create')
            ->with('/path/to/install', 'JaduMigrationsXFP')
            ->andReturn($fileMigrationVersionStorage)
            ->once();

        $configuration = $this->configurationFactory->createFileConfiguration(
            array(
                'name' => 'jadu/xfp',
                'namespace' => 'Migrations',
                'table' => 'JaduMigrationsXFP',
                'directory' => 'upgrades',
            ),
            '/path/to/patch',
            '/path/to/install',
            true
        );

        $this->assertSame($connection, $configuration->getConnection());
        $this->assertSame('jadu/xfp', $configuration->getName());
        $this->assertSame('Migrations', $configuration->getMigrationsNamespace());
        $this->assertSame('JaduMigrationsXFP', $configuration->getMigrationsTableName());
        $this->assertSame('/path/to/patch/upgrades/filesystem', $configuration->getMigrationsDirectory());
        $this->assertSame('/path/to/install', $configuration->getJaduPath());
        $this->assertSame($fileMigrationVersionStorage, $configuration->getVersionStorage());
    }

    public function testInitialisesFileMigrationVersionStorage()
    {
        $connection = Mockery::mock('Doctrine\DBAL\Connection');
        $this->connectionFactory->shouldReceive('getConnection')
            ->with('/path/to/install')
            ->andReturn($connection)
            ->once();

        $fileMigrationVersionStorage = Mockery::mock('Meteor\Migrations\Version\FileMigrationVersionStorage', array(
            'isInitialised' => false,
        ));

        $this->fileMigrationVersionStorageFactory->shouldReceive('create')
            ->with('/path/to/install', 'JaduMigrationsXFP')
            ->andReturn($fileMigrationVersionStorage)
            ->once();

        $this->versionFileManager->shouldReceive('getCurrentVersion')
            ->with('/path/to/install', 'JaduMigrationsXFP', VersionFileManager::FILE_MIGRATION)
            ->andReturn('12345')
            ->once();

        $fileMigrationVersionStorage->shouldReceive('initialise')
            ->with(Mockery::type('Meteor\Migrations\Configuration\FileConfiguration'), '12345')
            ->once();

        $configuration = $this->configurationFactory->createFileConfiguration(
            array(
                'name' => 'jadu/xfp',
                'namespace' => 'Migrations',
                'table' => 'JaduMigrationsXFP',
                'directory' => 'upgrades',
            ),
            '/path/to/patch',
            '/path/to/install',
            true
        );
    }
}
