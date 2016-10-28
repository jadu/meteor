<?php

namespace Meteor\Package\Migrations;

use Meteor\IO\NullIO;
use Mockery;

class MigrationsCopierTest extends \PHPUnit_Framework_TestCase
{
    private $filesystem;
    private $io;
    private $migrationsCopier;

    public function setUp()
    {
        $this->filesystem = Mockery::mock('Meteor\Filesystem\Filesystem');
        $this->io = new NullIO();

        $this->migrationsCopier = new MigrationsCopier($this->filesystem, $this->io);
    }

    public function testReturnsConfig()
    {
        $config = [
            'name' => 'test',
        ];

        $this->assertSame($config, $this->migrationsCopier->copy('working', 'temp', $config));
    }

    public function testCopiesMigrations()
    {
        $config = [
            'name' => 'test',
            'migrations' => [
                'directory' => 'upgrades/migrations',
            ],
        ];

        $this->filesystem->shouldReceive('copyDirectory')
            ->with('working/upgrades/migrations', 'temp/migrations/test')
            ->once();

        $this->migrationsCopier->copy('working', 'temp', $config);
    }

    public function testUpdatesMigrationsConfig()
    {
        $config = [
            'name' => 'test',
            'migrations' => [
                'directory' => 'upgrades/migrations',
            ],
        ];

        $this->filesystem->shouldReceive('copyDirectory');

        $this->assertSame([
            'name' => 'test',
            'migrations' => [
                'directory' => 'migrations/test',
            ],
        ], $this->migrationsCopier->copy('working', 'temp', $config));
    }
}
