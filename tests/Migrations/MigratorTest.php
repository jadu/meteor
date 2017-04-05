<?php

namespace Meteor\Migrations;

use Meteor\IO\NullIO;
use Mockery;

class MigratorTest extends \PHPUnit_Framework_TestCase
{
    private $configurationFactory;
    private $io;
    private $migrator;

    public function setUp()
    {
        $this->configurationFactory = Mockery::mock('Meteor\Migrations\Configuration\ConfigurationFactory');
        $this->io = new NullIO();

        $this->migrator = new Migrator($this->configurationFactory, $this->io);
    }

    private function createVersion($versionString)
    {
        return Mockery::mock('Doctrine\DBAL\Migrations\Version', [
            'getVersion' => $versionString,
            '__toString' => $versionString,
            'getTime' => 5,
        ]);
    }

    public function testMigrate()
    {
        $config = [];

        $version1 = $this->createVersion('20160701000000');
        $version2 = $this->createVersion('20160702000000');
        $version3 = $this->createVersion('20160703000000');

        $configuration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration', [
            'getMigrations' => [
                $version1->getVersion() => $version1,
                $version2->getVersion() => $version2,
                $version3->getVersion() => $version3,
            ],
            'getMigratedVersions' => [
                $version2->getVersion(),
                $version3->getVersion(),
            ],
            'getAvailableVersions' => [
                $version1->getVersion(),
                $version2->getVersion(),
                $version3->getVersion(),
            ],
            'getCurrentVersion' => $version1->getVersion(),
            'getMigrationsToExecute' => [
                $version2,
                $version3,
            ],
            'resolveVersionAlias' => $version3->getVersion(),
        ]);

        $configuration->shouldReceive('formatVersion')
            ->andReturnUsing(function ($version) {
                return (string) $version;
            });

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, 'patch', 'install')
            ->andReturn($configuration)
            ->once();

        $version1->shouldReceive('execute')
            ->never();

        $version2->shouldReceive('execute')
            ->with('up', false, false)
            ->once();

        $version3->shouldReceive('execute')
            ->with('up', false, false)
            ->once();

        $this->assertTrue($this->migrator->migrate('patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, 'latest', false));
    }

    public function testMigrateHaltsWhenUnavailableMigrationsFound()
    {
        $config = [];

        $version1 = $this->createVersion('20160701000000');
        $version2 = $this->createVersion('20160702000000');
        $version3 = $this->createVersion('20160703000000');

        $configuration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration', [
            'getMigrations' => [
                $version1->getVersion() => $version1,
                $version2->getVersion() => $version2,
                $version3->getVersion() => $version3,
            ],
            'getMigratedVersions' => [
                $version2->getVersion(),
                $version3->getVersion(),
            ],
            'getAvailableVersions' => [
                $version1->getVersion(),
                $version3->getVersion(),
            ],
            'getCurrentVersion' => $version1->getVersion(),
            'getMigrationsToExecute' => [
                $version2,
                $version3,
            ],
            'resolveVersionAlias' => $version3->getVersion(),
        ]);

        $configuration->shouldReceive('formatVersion')
            ->andReturnUsing(function ($version) {
                return (string) $version;
            });

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, 'patch', 'install')
            ->andReturn($configuration)
            ->once();

        $version1->shouldReceive('execute')
            ->never();

        $this->assertFalse($this->migrator->migrate('patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, 'latest', false));
    }

    public function testMigrateReturnsTrueWhenNoMigrationsToExecute()
    {
        $config = [];

        $version1 = $this->createVersion('20160701000000');
        $version2 = $this->createVersion('20160702000000');
        $version3 = $this->createVersion('20160703000000');

        $configuration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration', [
            'getMigrations' => [
                $version1->getVersion() => $version1,
                $version2->getVersion() => $version2,
                $version3->getVersion() => $version3,
            ],
            'getMigratedVersions' => [
                $version1->getVersion(),
                $version2->getVersion(),
                $version3->getVersion(),
            ],
            'getAvailableVersions' => [
                $version1->getVersion(),
                $version2->getVersion(),
                $version3->getVersion(),
            ],
            'getCurrentVersion' => $version3->getVersion(),
            'getMigrationsToExecute' => [],
            'resolveVersionAlias' => $version3->getVersion(),
        ]);

        $configuration->shouldReceive('formatVersion')
            ->andReturnUsing(function ($version) {
                return (string) $version;
            });

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, 'patch', 'install')
            ->andReturn($configuration)
            ->once();

        $version1->shouldReceive('execute')
            ->never();

        $version2->shouldReceive('execute')
            ->never();

        $version3->shouldReceive('execute')
            ->never();

        $this->assertTrue($this->migrator->migrate('patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, 'latest', false));
    }

    public function testMigrateIgnoresUnavailableMigrations()
    {
        $config = [];

        $version1 = $this->createVersion('20160701000000');
        $version2 = $this->createVersion('20160702000000');
        $version3 = $this->createVersion('20160703000000');

        $configuration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration', [
            'getMigrations' => [
                $version1->getVersion() => $version1,
                $version2->getVersion() => $version2,
                $version3->getVersion() => $version3,
            ],
            'getMigratedVersions' => [
                $version2->getVersion(),
                $version3->getVersion(),
            ],
            'getAvailableVersions' => [
                $version1->getVersion(),
                $version3->getVersion(),
            ],
            'getCurrentVersion' => $version1->getVersion(),
            'getMigrationsToExecute' => [
                $version3,
            ],
            'resolveVersionAlias' => $version3->getVersion(),
        ]);

        $configuration->shouldReceive('formatVersion')
            ->andReturnUsing(function ($version) {
                return (string) $version;
            });

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, 'patch', 'install')
            ->andReturn($configuration)
            ->once();

        $version1->shouldReceive('execute')
            ->never();

        $version3->shouldReceive('execute')
            ->with('up', false, false)
            ->once();

        $this->assertTrue($this->migrator->migrate('patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, 'latest', true));
    }

    public function testExecuteUp()
    {
        $config = [];

        $configuration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration');
        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, 'patch', 'install')
            ->andReturn($configuration)
            ->once();

        $version = $this->createVersion('20160701000000');
        $configuration->shouldReceive('getVersion')
            ->with('20160701000000')
            ->andReturn($version)
            ->once();

        $version->shouldReceive('execute')
            ->with('up', false, false)
            ->once();

        $this->assertTrue($this->migrator->execute('patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, '20160701000000', 'up'));
    }

    public function testExecuteDown()
    {
        $config = [];

        $configuration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration');
        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, 'patch', 'install')
            ->andReturn($configuration)
            ->once();

        $version = $this->createVersion('20160701000000');
        $configuration->shouldReceive('getVersion')
            ->with('20160701000000')
            ->andReturn($version)
            ->once();

        $version->shouldReceive('execute')
            ->with('down', false, false)
            ->once();

        $this->assertTrue($this->migrator->execute('patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, '20160701000000', 'down'));
    }
}
