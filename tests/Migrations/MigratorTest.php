<?php

namespace Meteor\Migrations;

use Doctrine\Migrations\Exception\UnknownMigrationVersion;
use Doctrine\Migrations\Version\ExecutionResult;
use Doctrine\Migrations\Version\Version;
use Meteor\IO\NullIO;
use Meteor\Migrations\Configuration\ConfigurationFactory;
use Meteor\Migrations\Configuration\DatabaseConfiguration;
use Mockery;
use PHPUnit\Framework\TestCase;

class MigratorTest extends TestCase
{
    private $configurationFactory;
    private $io;
    private $migrator;

    protected function setUp(): void
    {
        $this->configurationFactory = Mockery::mock(ConfigurationFactory::class);
        $this->io = new NullIO();

        $this->migrator = new Migrator($this->configurationFactory, $this->io);
    }

    private function createVersion($versionString)
    {
        return Mockery::mock(Version::class, [
            'getVersion' => $versionString,
            '__toString' => $versionString,
        ]);
    }

    public function testMigrate()
    {
        $config = ['directory' => 'upgrades'];

        $version1 = $this->createVersion('20160701000000');
        $version2 = $this->createVersion('20160702000000');
        $version3 = $this->createVersion('20160703000000');

        $configuration = Mockery::mock(DatabaseConfiguration::class, [
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

        $configuration->shouldReceive('getDateTime')
            ->andReturnUsing(function ($version) {
                return (string) $version;
            });

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, __DIR__ . '/Configuration/Fixtures/with_migrations/patch', 'install')
            ->andReturn($configuration)
            ->once();

        $version1->shouldReceive('execute')
            ->never();

        $version2->shouldReceive('execute')
            ->with('up')
            ->andReturn(Mockery::mock(ExecutionResult::class, [
                'getTime' => 5,
            ]))
            ->once();

        $version3->shouldReceive('execute')
            ->with('up')
            ->andReturn(Mockery::mock(ExecutionResult::class, [
                'getTime' => 5,
            ]))
            ->once();

        static::assertTrue($this->migrator->migrate(__DIR__ . '/Configuration/Fixtures/with_migrations/patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, 'latest', false));
    }

    public function testMigrateReturnsTrueWhenNoFilesystemDirFound()
    {
        $config = ['directory' => 'upgrades'];
        static::assertTrue($this->migrator->migrate(__DIR__ . '/Configuration/Fixtures/empty/patch', 'install', $config, MigrationsConstants::TYPE_FILE, 'latest', false));
    }

    public function testMigrateHaltsWhenUnavailableMigrationsFound()
    {
        $config = ['directory' => 'upgrades'];

        $version1 = $this->createVersion('20160701000000');
        $version2 = $this->createVersion('20160702000000');
        $version3 = $this->createVersion('20160703000000');

        $configuration = Mockery::mock(DatabaseConfiguration::class, [
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

        $configuration->shouldReceive('getDateTime')
            ->andReturnUsing(function ($version) {
                return (string) $version;
            });

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, __DIR__ . '/Configuration/Fixtures/with_migrations/patch', 'install')
            ->andReturn($configuration)
            ->once();

        $version1->shouldReceive('execute')
            ->never();

        static::assertFalse($this->migrator->migrate(__DIR__ . '/Configuration/Fixtures/with_migrations/patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, 'latest', false));
    }

    public function testMigrateReturnsTrueWhenNoMigrationsToExecute()
    {
        $config = ['directory' => 'upgrades'];

        $version1 = $this->createVersion('20160701000000');
        $version2 = $this->createVersion('20160702000000');
        $version3 = $this->createVersion('20160703000000');

        $configuration = Mockery::mock(DatabaseConfiguration::class, [
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

        $configuration->shouldReceive('getDateTime')
            ->andReturnUsing(function ($version) {
                return (string) $version;
            });

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, __DIR__ . '/Configuration/Fixtures/with_migrations/patch', 'install')
            ->andReturn($configuration)
            ->once();

        $version1->shouldReceive('execute')
            ->never();

        $version2->shouldReceive('execute')
            ->never();

        $version3->shouldReceive('execute')
            ->never();

        static::assertTrue($this->migrator->migrate(__DIR__ . '/Configuration/Fixtures/with_migrations/patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, 'latest', false));
    }

    public function testMigrateIgnoresUnavailableMigrations()
    {
        $config = ['directory' => 'upgrades'];

        $version1 = $this->createVersion('20160701000000');
        $version2 = $this->createVersion('20160702000000');
        $version3 = $this->createVersion('20160703000000');

        $configuration = Mockery::mock(DatabaseConfiguration::class, [
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

        $configuration->shouldReceive('getDateTime')
            ->andReturnUsing(function ($version) {
                return (string) $version;
            });

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, __DIR__ . '/Configuration/Fixtures/with_migrations/patch', 'install')
            ->andReturn($configuration)
            ->once();

        $version1->shouldReceive('execute')
            ->never();

        $version3->shouldReceive('execute')
            ->with('up')
            ->andReturn(Mockery::mock(ExecutionResult::class, [
                'getTime' => 5,
            ]))
            ->once();

        static::assertTrue($this->migrator->migrate(__DIR__ . '/Configuration/Fixtures/with_migrations/patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, 'latest', true));
    }

    public function testMigrateThrowsWhenUnknownMigration()
    {
        $this->expectException(UnknownMigrationVersion::class);
        $this->expectExceptionMessage('Could not find migration version 2');

        $patchDirectory = __DIR__ . '/Configuration/Fixtures/with_migrations/patch';

        $config = [
            'directory' => 'upgrades',
        ];

        $configuration = Mockery::mock(DatabaseConfiguration::class, [
            'getMigrations' => [],
            'getMigratedVersions' => [],
            'getAvailableVersions' => [],
            'getCurrentVersion' => 0,
            'getMigrationsToExecute' => [],
            'resolveVersionAlias' => 2,
        ]);

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, $patchDirectory, 'install')
            ->andReturn($configuration)
            ->once();

        $this->migrator->migrate($patchDirectory, 'install', $config, MigrationsConstants::TYPE_DATABASE, 'latest', true);
    }

    public function testExecuteUp()
    {
        $config = [];

        $configuration = Mockery::mock(DatabaseConfiguration::class);
        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, __DIR__ . '/Configuration/Fixtures/with_migrations/patch', 'install')
            ->andReturn($configuration)
            ->once();

        $version = $this->createVersion('20160701000000');
        $configuration->shouldReceive('getVersion')
            ->with('20160701000000')
            ->andReturn($version)
            ->once();

        $version->shouldReceive('execute')
            ->with('up')
            ->andReturn(Mockery::mock(ExecutionResult::class, [
                'getTime' => 5,
            ]))
            ->once();

        static::assertTrue($this->migrator->execute(__DIR__ . '/Configuration/Fixtures/with_migrations/patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, '20160701000000', 'up'));
    }

    public function testExecuteDown()
    {
        $config = [];

        $configuration = Mockery::mock(DatabaseConfiguration::class);
        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, __DIR__ . '/Configuration/Fixtures/with_migrations/patch', 'install')
            ->andReturn($configuration)
            ->once();

        $version = $this->createVersion('20160701000000');
        $configuration->shouldReceive('getVersion')
            ->with('20160701000000')
            ->andReturn($version)
            ->once();

        $version->shouldReceive('execute')
            ->with('down')
            ->once();

        static::assertTrue($this->migrator->execute(__DIR__ . '/Configuration/Fixtures/with_migrations/patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, '20160701000000', 'down'));
    }
}
