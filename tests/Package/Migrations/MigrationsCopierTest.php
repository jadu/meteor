<?php

namespace Meteor\Package\Migrations;

use Meteor\IO\NullIO;
use Mockery;
use PHPUnit\Framework\TestCase;

class MigrationsCopierTest extends TestCase
{
    private $filesystem;
    private $io;
    private $migrationsCopier;

    protected function setUp(): void
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

        static::assertSame($config, $this->migrationsCopier->copy('working', 'temp', $config));
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

        static::assertSame([
            'name' => 'test',
            'migrations' => [
                'directory' => 'migrations/test',
            ],
        ], $this->migrationsCopier->copy('working', 'temp', $config));
    }
}
