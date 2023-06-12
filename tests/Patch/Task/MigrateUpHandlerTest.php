<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use Meteor\Migrations\MigrationsConstants;
use Mockery;
use PHPUnit\Framework\TestCase;

class MigrateUpHandlerTest extends TestCase
{
    private $migrator;
    private $io;
    private $handler;

    protected function setUp(): void
    {
        $this->migrator = Mockery::mock('Meteor\Migrations\Migrator');
        $this->io = new NullIO();

        $this->handler = new MigrateUpHandler($this->migrator, $this->io);
    }

    public function testRunsFileMigrationsIfConfigured()
    {
        $config = [
            'name' => 'root',
            'migrations' => [
                'table' => 'Migrations',
            ],
        ];

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['migrations'], MigrationsConstants::TYPE_FILE, 'latest', false)
            ->andReturn(true)
            ->once();
        $this->migrator->shouldReceive('validateConfiguration')
            ->with(MigrationsConstants::TYPE_FILE, $config['migrations'], 'working')
            ->andReturn(true)
            ->once();

        $task = new MigrateUp('working', 'install', MigrationsConstants::TYPE_FILE, false);
        $this->handler->handle($task, $config);
    }

    public function testNoFailureIfNoFilesystemDir()
    {
        $config = [
            'name' => 'root',
            'migrations' => [
                'table' => 'Migrations',
            ],
        ];

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['migrations'], MigrationsConstants::TYPE_FILE, 'latest', false)
            ->andReturn(true)
            ->never();
        $this->migrator->shouldReceive('validateConfiguration')
            ->with(MigrationsConstants::TYPE_FILE, $config['migrations'], 'working')
            ->andReturn(false)
            ->once();

        $task = new MigrateUp('working', 'install', MigrationsConstants::TYPE_FILE, false);
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

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['migrations'], MigrationsConstants::TYPE_DATABASE, 'latest', false)
            ->andReturn(true)
            ->once();
        $this->migrator->shouldReceive('validateConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config['migrations'], 'working')
            ->andReturn(true)
            ->once();

        $task = new MigrateUp('working', 'install', MigrationsConstants::TYPE_DATABASE, false);
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

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['combined'][0]['migrations'], MigrationsConstants::TYPE_FILE, 'latest', false)
            ->andReturn(true)
            ->once();
        $this->migrator->shouldReceive('validateConfiguration')
            ->with(MigrationsConstants::TYPE_FILE, $config['combined'][0]['migrations'], 'working')
            ->andReturn(true)
            ->once();

        $task = new MigrateUp('working', 'install', MigrationsConstants::TYPE_FILE, false);
        $this->handler->handle($task, $config);
    }

    public function testRunMigrationsInOrder()
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

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['combined'][0]['migrations'], MigrationsConstants::TYPE_FILE, 'latest', false)
            ->andReturn(true)
            ->ordered()
            ->once();
        $this->migrator->shouldReceive('validateConfiguration')
            ->with(MigrationsConstants::TYPE_FILE, $config['combined'][0]['migrations'], 'working')
            ->andReturn(true)
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['combined'][1]['migrations'], MigrationsConstants::TYPE_FILE, 'latest', false)
            ->andReturn(true)
            ->ordered()
            ->once();
        $this->migrator->shouldReceive('validateConfiguration')
            ->with(MigrationsConstants::TYPE_FILE, $config['combined'][1]['migrations'], 'working')
            ->andReturn(true)
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['migrations'], MigrationsConstants::TYPE_FILE, 'latest', false)
            ->andReturn(true)
            ->ordered()
            ->once();
        $this->migrator->shouldReceive('validateConfiguration')
            ->with(MigrationsConstants::TYPE_FILE, $config['migrations'], 'working')
            ->andReturn(true)
            ->once();

        $task = new MigrateUp('working', 'install', MigrationsConstants::TYPE_FILE, false);
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

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['migrations'], MigrationsConstants::TYPE_FILE, 'latest', false)
            ->andReturn(false)
            ->once();
        $this->migrator->shouldReceive('validateConfiguration')
            ->with(MigrationsConstants::TYPE_FILE, $config['migrations'], 'working')
            ->andReturn(true)
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['combined'][0]['migrations'], MigrationsConstants::TYPE_FILE, 'latest', false)
            ->never();
        $this->migrator->shouldReceive('validateConfiguration')
            ->with(MigrationsConstants::TYPE_FILE, $config['combined'][0]['migrations'], 'working')
            ->andReturn(true)
            ->once();

        $task = new MigrateUp('working', 'install', MigrationsConstants::TYPE_FILE, false);
        $this->handler->handle($task, $config);
    }

    public function testHaltsHandlerIfCombinedPackageMigrationsFail()
    {
        $config = [
            'combined' => [
                [
                    'name' => 'test',
                    'migrations' => [
                        'table' => 'Migrations',
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

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['combined'][0]['migrations'], MigrationsConstants::TYPE_FILE, 'latest', false)
            ->andReturn(false)
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['combined'][1]['migrations'], MigrationsConstants::TYPE_FILE, 'latest', false)
            ->never();
        $this->migrator->shouldReceive('validateConfiguration')
            ->with(MigrationsConstants::TYPE_FILE, $config['combined'][0]['migrations'], 'working')
            ->andReturn(true)
            ->once();
        $this->migrator->shouldReceive('validateConfiguration')
            ->with(MigrationsConstants::TYPE_FILE, $config['combined'][1]['migrations'], 'working')
            ->andReturn(true)
            ->once();

        $task = new MigrateUp('working', 'install', MigrationsConstants::TYPE_FILE, false);
        $this->handler->handle($task, $config);
    }
}
