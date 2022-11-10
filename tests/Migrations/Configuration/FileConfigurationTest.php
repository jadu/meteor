<?php

namespace Meteor\Migrations\Configuration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\Migrations\Version\Version;
use Meteor\Migrations\Version\FileMigrationVersion;
use Meteor\Migrations\Version\FileMigrationVersionStorage;
use Mockery;
use PHPUnit\Framework\TestCase;

class FileConfigurationTest extends TestCase
{
    private $configuration;
    private $versionStorage;

    protected function setUp(): void
    {
        $this->configuration = new FileConfiguration(Mockery::mock(Connection::class, [
            // Stub methods to satisfy `new Version`
            'getSchemaManager' => Mockery::mock(AbstractSchemaManager::class),
            'getDatabasePlatform' => Mockery::mock(AbstractPlatform::class),
        ]));

        $this->versionStorage = Mockery::mock(FileMigrationVersionStorage::class);
        $this->configuration->setVersionStorage($this->versionStorage);
    }

    /**
     * Ensure the method exists as it is used by old migrations.
     */
    public function testCanSetJaduPath()
    {
        $this->configuration->setJaduPath('/var/www/jadu');

        static::assertSame('/var/www/jadu', $this->configuration->getJaduPath());
    }

    public function testCanSetVersionStorage()
    {
        $this->configuration->setVersionStorage($this->versionStorage);

        static::assertSame($this->versionStorage, $this->configuration->getVersionStorage());
    }

    public function testHasVersionMigrated()
    {
        $version = Mockery::mock(Version::class, [
            'getVersion' => '1',
        ]);

        $this->versionStorage->shouldReceive('hasVersionMigrated')
            ->with('1')
            ->andReturn(true)
            ->once();

        static::assertTrue($this->configuration->hasVersionMigrated($version));
    }

    public function testGetMigratedVersions()
    {
        $versionStrings = ['20160701000000', '20160701000001', '20160701000002', '20160701000003'];
        $this->versionStorage->shouldReceive('getMigratedVersions')
            ->andReturn($versionStrings)
            ->once();

        static::assertSame($versionStrings, $this->configuration->getMigratedVersions());
    }

    public function testGetCurrentVersion()
    {
        $this->versionStorage->shouldReceive('getCurrentVersion')
            ->andReturn('1')
            ->once();

        static::assertSame('1', $this->configuration->getCurrentVersion());
    }

    public function testGetNumberOfExecutedMigrations()
    {
        $this->versionStorage->shouldReceive('getNumberOfExecutedMigrations')
            ->andReturn(5)
            ->once();

        static::assertSame(5, $this->configuration->getNumberOfExecutedMigrations());
    }

    public function testCreateMigrationTableStubbed()
    {
        // It should not try to execute any database queries on the connection
        static::assertTrue($this->configuration->createMigrationTable());
    }

    public function testRegisterMigrationReturnsInstanceOfFileMigrationVersion()
    {
        $version = $this->configuration->registerMigration('1', 'stdClass');

        static::assertInstanceOf(FileMigrationVersion::class, $version);
    }
}
