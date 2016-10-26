<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use Meteor\Migrations\MigrationsConstants;
use Mockery;

class MigrateUpHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $migrator;
    private $io;
    private $handler;

    public function setUp()
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
            ->with('working', 'install', $config['migrations'], MigrationsConstants::TYPE_FILE, 'latest')
            ->andReturn(true)
            ->once();

        $task = new MigrateUp('working', 'install', MigrationsConstants::TYPE_FILE);
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
            ->with('working', 'install', $config['migrations'], MigrationsConstants::TYPE_DATABASE, 'latest')
            ->andReturn(true)
            ->once();

        $task = new MigrateUp('working', 'install', MigrationsConstants::TYPE_DATABASE);
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
            ->with('working', 'install', $config['combined'][0]['migrations'], MigrationsConstants::TYPE_FILE, 'latest')
            ->andReturn(true)
            ->once();

        $task = new MigrateUp('working', 'install', MigrationsConstants::TYPE_FILE);
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
            ->with('working', 'install', $config['combined'][0]['migrations'], MigrationsConstants::TYPE_FILE, 'latest')
            ->andReturn(true)
            ->ordered()
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['combined'][1]['migrations'], MigrationsConstants::TYPE_FILE, 'latest')
            ->andReturn(true)
            ->ordered()
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['migrations'], MigrationsConstants::TYPE_FILE, 'latest')
            ->andReturn(true)
            ->ordered()
            ->once();

        $task = new MigrateUp('working', 'install', MigrationsConstants::TYPE_FILE);
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
            ->with('working', 'install', $config['migrations'], MigrationsConstants::TYPE_FILE, 'latest')
            ->andReturn(false)
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['combined'][0]['migrations'], MigrationsConstants::TYPE_FILE, 'latest')
            ->never();

        $task = new MigrateUp('working', 'install', MigrationsConstants::TYPE_FILE);
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
            ->with('working', 'install', $config['combined'][0]['migrations'], MigrationsConstants::TYPE_FILE, 'latest')
            ->andReturn(false)
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with('working', 'install', $config['combined'][1]['migrations'], MigrationsConstants::TYPE_FILE, 'latest')
            ->never();

        $task = new MigrateUp('working', 'install', MigrationsConstants::TYPE_FILE);
        $this->handler->handle($task, $config);
    }
}
