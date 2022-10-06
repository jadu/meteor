<?php

namespace Meteor\Migrations\Configuration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Meteor\IO\NullIO;
use Meteor\Migrations\Connection\ConnectionFactory;
use Meteor\Migrations\Version\FileMigrationVersionStorage;
use Meteor\Migrations\Version\FileMigrationVersionStorageFactory;
use Meteor\Migrations\Version\VersionFileManager;
use Mockery;
use PHPUnit\Framework\TestCase;

class ConfigurationFactoryTest extends TestCase
{
    private $connectionFactory;
    private $fileMigrationVersionStorageFactory;
    private $versionFileManager;
    private $io;
    private $configurationFactory;

    protected function setUp(): void
    {
        $this->connectionFactory = Mockery::mock(ConnectionFactory::class);
        $this->fileMigrationVersionStorageFactory = Mockery::mock(FileMigrationVersionStorageFactory::class);
        $this->versionFileManager = Mockery::mock(VersionFileManager::class);
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
        $connection = Mockery::mock(Connection::class, [
            'getDatabasePlatform' => Mockery::mock(MySqlPlatform::class),
            'getSchemaManager' => Mockery::mock(AbstractSchemaManager::class),
        ]);
        $this->connectionFactory->shouldReceive('getConnection')
            ->with(__DIR__ . '/Fixtures/empty/install')
            ->andReturn($connection)
            ->once();

        $configuration = $this->configurationFactory->createDatabaseConfiguration(
            [
                'name' => 'jadu/xfp',
                'namespace' => 'Migrations',
                'table' => 'JaduMigrationsXFP',
                'directory' => 'upgrades',
            ],
            __DIR__ . '/Fixtures/empty/patch',
            __DIR__ . '/Fixtures/empty/install',
            true
        );

        static::assertSame($connection, $configuration->getConnection());
        static::assertSame('jadu/xfp', $configuration->getName());
        static::assertSame('Migrations', $configuration->getMigrationsNamespace());
        static::assertSame('JaduMigrationsXFP', $configuration->getMigrationsTableName());
        static::assertSame(__DIR__ . '/Fixtures/empty/patch/upgrades', $configuration->getMigrationsDirectory());
        static::assertSame(__DIR__ . '/Fixtures/empty/install', $configuration->getJaduPath());
    }

    public function testCreateDatabaseConfigurationWithMigrationsOnlyFindsDatabaseMigrations()
    {
        $connection = Mockery::mock(Connection::class, [
            'getDatabasePlatform' => Mockery::mock(MySqlPlatform::class),
            'getSchemaManager' => Mockery::mock(AbstractSchemaManager::class),
        ]);
        $this->connectionFactory->shouldReceive('getConnection')
            ->with(__DIR__ . '/Fixtures/with_migrations/install')
            ->andReturn($connection)
            ->once();

        $configuration = $this->configurationFactory->createDatabaseConfiguration(
            [
                'name' => 'jadu/xfp',
                'namespace' => 'Doctrine\Migrations',
                'table' => 'JaduMigrationsXFP',
                'directory' => 'upgrades',
            ],
            __DIR__ . '/Fixtures/with_migrations/patch',
            __DIR__ . '/Fixtures/with_migrations/install',
            true
        );

        $versions = [];

        foreach ($configuration->getMigrations() as $migration) {
            $versions[] = $migration->getVersion();
        }

        static::assertEquals(['1234567890'], $versions);
    }

    public function testCreateFileConfiguration()
    {
        $connection = Mockery::mock(Connection::class, [
            'getDatabasePlatform' => Mockery::mock(MySqlPlatform::class),
            'getSchemaManager' => Mockery::mock(AbstractSchemaManager::class),
        ]);
        $this->connectionFactory->shouldReceive('getConnection')
            ->with(__DIR__ . '/Fixtures/empty/install')
            ->andReturn($connection)
            ->once();

        $fileMigrationVersionStorage = Mockery::mock(FileMigrationVersionStorage::class, [
            'isInitialised' => true,
        ]);
        $this->fileMigrationVersionStorageFactory->shouldReceive('create')
            ->with(__DIR__ . '/Fixtures/empty/install', 'JaduMigrationsXFP')
            ->andReturn($fileMigrationVersionStorage)
            ->once();

        $configuration = $this->configurationFactory->createFileConfiguration(
            [
                'name' => 'jadu/xfp',
                'namespace' => 'Migrations',
                'table' => 'JaduMigrationsXFP',
                'directory' => 'upgrades',
            ],
            __DIR__ . '/Fixtures/empty/patch',
            __DIR__ . '/Fixtures/empty/install',
            true
        );

        static::assertSame($connection, $configuration->getConnection());
        static::assertSame('jadu/xfp', $configuration->getName());
        static::assertSame('Migrations', $configuration->getMigrationsNamespace());
        static::assertSame('JaduMigrationsXFP', $configuration->getMigrationsTableName());
        static::assertSame(__DIR__ . '/Fixtures/empty/patch/upgrades/filesystem', $configuration->getMigrationsDirectory());
        static::assertSame(__DIR__ . '/Fixtures/empty/install', $configuration->getJaduPath());
        static::assertSame($fileMigrationVersionStorage, $configuration->getVersionStorage());
    }

    public function testCreateFileConfigurationWithMigrationsOnlyIncludesFileMigrations()
    {
        $connection = Mockery::mock(Connection::class, [
            'getDatabasePlatform' => Mockery::mock(MySqlPlatform::class),
            'getSchemaManager' => Mockery::mock(AbstractSchemaManager::class),
        ]);
        $this->connectionFactory->shouldReceive('getConnection')
            ->with(__DIR__ . '/Fixtures/with_migrations/install')
            ->andReturn($connection)
            ->once();

        $fileMigrationVersionStorage = Mockery::mock(FileMigrationVersionStorage::class, [
            'isInitialised' => true,
        ]);
        $this->fileMigrationVersionStorageFactory->shouldReceive('create')
            ->with(__DIR__ . '/Fixtures/with_migrations/install', 'JaduMigrationsXFP')
            ->andReturn($fileMigrationVersionStorage)
            ->once();

        $configuration = $this->configurationFactory->createFileConfiguration(
            [
                'name' => 'jadu/xfp',
                'namespace' => 'Doctrine\Migrations',
                'table' => 'JaduMigrationsXFP',
                'directory' => 'upgrades',
            ],
            __DIR__ . '/Fixtures/with_migrations/patch',
            __DIR__ . '/Fixtures/with_migrations/install',
            true
        );

        $versions = [];

        foreach ($configuration->getMigrations() as $migration) {
            $versions[] = $migration->getVersion();
        }

        static::assertEquals(['0987654321'], $versions);
    }

    public function testInitialisesFileMigrationVersionStorage()
    {
        $connection = Mockery::mock(Connection::class, [
            'getDatabasePlatform' => Mockery::mock(MySqlPlatform::class),
            'getSchemaManager' => Mockery::mock(AbstractSchemaManager::class),
        ]);

        $this->connectionFactory->shouldReceive('getConnection')
            ->with(__DIR__ . '/Fixtures/empty/install')
            ->andReturn($connection)
            ->once();

        $fileMigrationVersionStorage = Mockery::mock(FileMigrationVersionStorage::class, [
            'isInitialised' => false,
        ]);

        $this->fileMigrationVersionStorageFactory->shouldReceive('create')
            ->with(__DIR__ . '/Fixtures/empty/install', 'JaduMigrationsXFP')
            ->andReturn($fileMigrationVersionStorage)
            ->once();

        $this->versionFileManager->shouldReceive('getCurrentVersion')
            ->with(__DIR__ . '/Fixtures/empty/install', 'JaduMigrationsXFP', VersionFileManager::FILE_MIGRATION)
            ->andReturn('12345')
            ->once();

        $fileMigrationVersionStorage->shouldReceive('initialise')
            ->with(Mockery::type(FileConfiguration::class), '12345')
            ->once();

        $configuration = $this->configurationFactory->createFileConfiguration(
            [
                'name' => 'jadu/xfp',
                'namespace' => 'Migrations',
                'table' => 'JaduMigrationsXFP',
                'directory' => 'upgrades',
            ],
            __DIR__ . '/Fixtures/empty/patch',
            __DIR__ . '/Fixtures/empty/install',
            true
        );
    }
}
