<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use Meteor\Patch\Backup\Backup;
use Mockery;
use PHPUnit_Framework_TestCase;
use Meteor\Patch\Backup\BackupFinder;
use Meteor\Filesystem\Filesystem;

class LimitBackupsHandlerTest extends PHPUnit_Framework_TestCase
{

    protected $backupFinder;

    protected $filesystem;

    protected $io;

    protected $handler;

    public function setUp()
    {
        $this->backupFinder = Mockery::mock(BackupFinder::class);
        $this->filesystem = Mockery::mock(Filesystem::class);
        $this->io = new NullIO();

        $this->handler = new LimitBackupsHandler($this->backupFinder, $this->filesystem, $this->io);
    }

    public function testUnderLimit()
    {
        $config = ['name' => 'test'];

        $backups = [
            new Backup('backups/5', []),
            new Backup('backups/4', []),
            new Backup('backups/3', []),
            new Backup('backups/2', []),
            new Backup('backups/1', []),
        ];

        $this->backupFinder->shouldReceive('find')
            ->with('install', $config)
            ->andReturn($backups)
            ->once();

        $this->assertTrue($this->handler->handle(new LimitBackups('install', 10), $config));
    }

    public function testOverLimit()
    {
        $config = ['name' => 'test'];

        $backups = [
            new Backup('backups/5', []),
            new Backup('backups/4', []),
            new Backup('backups/3', []),
            new Backup('backups/2', []),
            new Backup('backups/1', []),
        ];

        $this->backupFinder->shouldReceive('find')
            ->with('install', $config)
            ->andReturn($backups)
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with('backups/2')
            ->once();


        $this->filesystem->shouldReceive('remove')
            ->with('backups/1')
            ->once();

        $this->assertTrue($this->handler->handle(new LimitBackups('install', 3), $config));
    }


    public function testInvalidBackups()
    {
        $config = ['name' => 'test'];

        $backups = [
            new Backup('backups/5', []),
            new Backup('backups/4', []),
            new Backup('backups/3', []),
            new Backup('backups/2', []),
            new Backup('backups/1', []),
        ];

        $this->backupFinder->shouldReceive('find')
            ->with('install', $config)
            ->andReturn($backups)
            ->once();

        $this->assertTrue($this->handler->handle(new LimitBackups('install', 0), $config));
    }
}
