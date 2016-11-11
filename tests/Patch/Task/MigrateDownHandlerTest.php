<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use Meteor\Migrations\MigrationsConstants;
use Mockery;

class MigrateDownHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $migrator;
    private $versionFileManager;
    private $io;
    private $handler;

    public function setUp()
    {
        $this->migrator = Mockery::mock('Meteor\Migrations\Migrator');
        $this->versionFileManager = Mockery::mock('Meteor\Migrations\Version\VersionFileManager');
        $this->io = new NullIO();

        $this->handler = new MigrateDownHandler($this->migrator, $this->versionFileManager, $this->io);
    }

    public function testRunsFileMigrationsIfConfigured()
    {
        $config = [
            'name' => 'root',
            'migrations' => [
                'table' => 'Migrations',
            ],
        ];

        $this->versionFileManager->shouldReceive('getCurrentVersion')
            ->with('backups/1', 'Migrations', 'FILE_SYSTEM_MIGRATION_NUMBER')
            ->andReturn('12345')
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['migrations'], MigrationsConstants::TYPE_FILE, '12345', false)
            ->andReturn(true)
            ->once();

        $task = new MigrateDown('backups/1', 'working', 'install', MigrationsConstants::TYPE_FILE, false);
        $this->handler->handle($task, $config);
    }

    public function testRunsDatabaseMigrationsIfConfigured()
    {
        $config = [
            'name' => 'root',
            'migrations' => [
                'table' => 'Migrations',
            ],
        ];

        $this->versionFileManager->shouldReceive('getCurrentVersion')
            ->with('backups/1', 'Migrations', 'MIGRATION_NUMBER')
            ->andReturn('12345')
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['migrations'], MigrationsConstants::TYPE_DATABASE, '12345', false)
            ->andReturn(true)
            ->once();

        $task = new MigrateDown('backups/1', 'working', 'install', MigrationsConstants::TYPE_DATABASE, false);
        $this->handler->handle($task, $config);
    }

    public function testRunsCombinedPackageMigrationsIfConfigured()
    {
        $config = [
            'combined' => [
                [
                    'name' => 'test',
                    'migrations' => [
                        'table' => 'Migrations',
                    ],
                ],
            ],
        ];

        $this->versionFileManager->shouldReceive('getCurrentVersion')
            ->with('backups/1', 'Migrations', 'FILE_SYSTEM_MIGRATION_NUMBER')
            ->andReturn('12345')
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['combined'][0]['migrations'], MigrationsConstants::TYPE_FILE, '12345', false)
            ->andReturn(true)
            ->once();

        $task = new MigrateDown('backups/1', 'working', 'install', MigrationsConstants::TYPE_FILE, false);
        $this->handler->handle($task, $config);
    }

    public function testRunsMigrationsInReverseOrder()
    {
        $config = [
            'name' => 'root',
            'migrations' => [
                'table' => 'Migrations',
            ],
            'combined' => [
                [
                    'name' => 'test1',
                    'migrations' => [
                        'table' => 'Migrations1',
                    ],
                ],
                [
                    'name' => 'test2',
                    'migrations' => [
                        'table' => 'Migrations2',
                    ],
                ],
            ],
        ];

        $this->versionFileManager->shouldReceive('getCurrentVersion')
            ->with('backups/1', $config['migrations']['table'], 'FILE_SYSTEM_MIGRATION_NUMBER')
            ->andReturn('12345')
            ->ordered()
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['migrations'], MigrationsConstants::TYPE_FILE, '12345', false)
            ->andReturn(true)
            ->ordered()
            ->once();

        $this->versionFileManager->shouldReceive('getCurrentVersion')
            ->with('backups/1', $config['combined'][1]['migrations']['table'], 'FILE_SYSTEM_MIGRATION_NUMBER')
            ->andReturn('12345')
            ->ordered()
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['combined'][1]['migrations'], MigrationsConstants::TYPE_FILE, '12345', false)
            ->andReturn(true)
            ->ordered()
            ->once();

        $this->versionFileManager->shouldReceive('getCurrentVersion')
            ->with('backups/1', $config['combined'][0]['migrations']['table'], 'FILE_SYSTEM_MIGRATION_NUMBER')
            ->andReturn('12345')
            ->ordered()
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['combined'][0]['migrations'], MigrationsConstants::TYPE_FILE, '12345', false)
            ->andReturn(true)
            ->ordered()
            ->once();

        $task = new MigrateDown('backups/1', 'working', 'install', MigrationsConstants::TYPE_FILE, false);
        $this->handler->handle($task, $config);
    }

    public function testHaltsHandlerIfMigrationsFail()
    {
        $config = [
            'name' => 'root',
            'migrations' => [
                'table' => 'Migrations',
            ],
            'combined' => [
                [
                    'name' => 'test',
                    'migrations' => [
                        'table' => 'Migrations',
                    ],
                ],
            ],
        ];

        $this->versionFileManager->shouldReceive('getCurrentVersion')
            ->andReturn('0');

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['migrations'], MigrationsConstants::TYPE_FILE, '0', false)
            ->andReturn(false)
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['combined'][0]['migrations'], MigrationsConstants::TYPE_FILE, '0', false)
            ->never();

        $task = new MigrateDown('backups/1', 'working', 'install', MigrationsConstants::TYPE_FILE, false);
        $this->handler->handle($task, $config);
    }

    public function testHaltsHandlerIfCombinedPackageMigrationsFail()
    {
        $config = [
            'combined' => [
                [
                    'name' => 'test',
                    'migrations' => [
                        'table' => 'Migrations1',
                    ],
                ],
                [
                    'name' => 'test2',
                    'migrations' => [
                        'table' => 'Migrations2',
                    ],
                ],
            ],
        ];

        $this->versionFileManager->shouldReceive('getCurrentVersion')
            ->andReturn('0');

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['combined'][1]['migrations'], MigrationsConstants::TYPE_FILE, '0', false)
            ->andReturn(false)
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['combined'][0]['migrations'], MigrationsConstants::TYPE_FILE, '0', false)
            ->never();

        $task = new MigrateDown('backups/1', 'working', 'install', MigrationsConstants::TYPE_FILE, false);
        $this->handler->handle($task, $config);
    }
}
