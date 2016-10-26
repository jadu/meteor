<?php

namespace Meteor\Migrations\Configuration;

use Mockery;

class FileConfigurationTest extends \PHPUnit_Framework_TestCase
{
    private $configuration;
    private $versionStorage;

    public function setUp()
    {
        $this->configuration = new FileConfiguration(Mockery::mock('Doctrine\DBAL\Connection', array(
            // Stub methods to satisfy `new Version`
            'getSchemaManager' => Mockery::mock('Doctrine\DBAL\Schema\AbstractSchemaManager'),
            'getDatabasePlatform' => Mockery::mock('Doctrine\DBAL\Platforms\AbstractPlatform'),
        )));

        $this->versionStorage = Mockery::mock('Meteor\Migrations\Version\FileMigrationVersionStorage');
        $this->configuration->setVersionStorage($this->versionStorage);
    }

    /**
     * Ensure the method exists as it is used by old migrations.
     */
    public function testCanSetJaduPath()
    {
        $this->configuration->setJaduPath('/var/www/jadu');

        $this->assertSame('/var/www/jadu', $this->configuration->getJaduPath());
    }

    public function testCanSetVersionStorage()
    {
        $this->configuration->setVersionStorage($this->versionStorage);

        $this->assertSame($this->versionStorage, $this->configuration->getVersionStorage());
    }

    public function testHasVersionMigrated()
    {
        $version = Mockery::mock('Doctrine\DBAL\Migrations\Version', array(
            'getVersion' => '1',
        ));

        $this->versionStorage->shouldReceive('hasVersionMigrated')
            ->with('1')
            ->andReturn(true)
            ->once();

        $this->assertTrue($this->configuration->hasVersionMigrated($version));
    }

    public function testGetMigratedVersions()
    {
        $versionStrings = array('20160701000000', '20160701000001', '20160701000002', '20160701000003');
        $this->versionStorage->shouldReceive('getMigratedVersions')
            ->andReturn($versionStrings)
            ->once();

        $this->assertSame($versionStrings, $this->configuration->getMigratedVersions());
    }

    public function testGetCurrentVersion()
    {
        $this->versionStorage->shouldReceive('getCurrentVersion')
            ->andReturn('1')
            ->once();

        $this->assertSame('1', $this->configuration->getCurrentVersion());
    }

    public function testGetNumberOfExecutedMigrations()
    {
        $this->versionStorage->shouldReceive('getNumberOfExecutedMigrations')
            ->andReturn(5)
            ->once();

        $this->assertSame(5, $this->configuration->getNumberOfExecutedMigrations());
    }

    public function testCreateMigrationTableStubbed()
    {
        // It should not try to execute any database queries on the connection
        $this->assertTrue($this->configuration->createMigrationTable());
    }

    public function testRegisterMigrationReturnsInstanceOfFileMigrationVersion()
    {
        $version = $this->configuration->registerMigration('1', 'stdClass');

        $this->assertInstanceOf('Meteor\Migrations\Version\FileMigrationVersion', $version);
    }
}
