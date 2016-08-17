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
        $config = array(
            'name' => 'test',
        );

        $this->assertSame($config, $this->migrationsCopier->copy('working', 'temp', $config));
    }

    public function testCopiesMigrations()
    {
        $config = array(
            'name' => 'test',
            'migrations' => array(
                'directory' => 'upgrades/migrations',
            ),
        );

        $this->filesystem->shouldReceive('copyDirectory')
            ->with('working/upgrades/migrations', 'temp/migrations/test')
            ->once();

        $this->migrationsCopier->copy('working', 'temp', $config);
    }

    public function testUpdatesMigrationsConfig()
    {
        $config = array(
            'name' => 'test',
            'migrations' => array(
                'directory' => 'upgrades/migrations',
            ),
        );

        $this->filesystem->shouldReceive('copyDirectory');

        $this->assertSame(array(
            'name' => 'test',
            'migrations' => array(
                'directory' => 'migrations/test',
            ),
        ), $this->migrationsCopier->copy('working', 'temp', $config));
    }
}
