<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use Meteor\Patch\Backup\Backup;
use Mockery;
use PHPUnit\Framework\TestCase;

class CheckDiskSpaceHandlerTest extends TestCase
{
    private $backupFinder;
    private $filesystem;
    private $io;
    private $handler;

    protected function setUp(): void
    {
        $this->backupFinder = Mockery::mock('Meteor\Patch\Backup\BackupFinder');
        $this->filesystem = Mockery::mock('Meteor\Filesystem\Filesystem');
        $this->io = new NullIO();

        $this->handler = new CheckDiskSpaceHandler($this->backupFinder, $this->filesystem, $this->io);
    }

    public function testPlentyOfSpace()
    {
        $GLOBALS['disk_total_space'] = 1048576000;
        $GLOBALS['disk_free_space'] = 1048576000;

        $config = ['name' => 'test'];
        static::assertTrue($this->handler->handle(new CheckDiskSpace('install', 'install/backups'), $config));
    }

    public function testWhenRunningLowOnSpace()
    {
        $GLOBALS['disk_total_space'] = 1048576000;
        $GLOBALS['disk_free_space'] = 419430400;

        $config = ['name' => 'test'];

        $this->backupFinder->shouldReceive('find')
            ->with('install/backups', 'install', $config)
            ->andReturn([]);

        static::assertFalse($this->handler->handle(new CheckDiskSpace('install', 'install/backups'), $config));
    }

    public function testRemovesOldBackupsWhenRunningLowOnSpace()
    {
        $GLOBALS['disk_total_space'] = 1048576000;
        $GLOBALS['disk_free_space'] = 419430400;

        $config = ['name' => 'test'];

        $backups = [
            new Backup('backups/5', []),
            new Backup('backups/4', []),
            new Backup('backups/3', []),
            new Backup('backups/2', []),
            new Backup('backups/1', []),
        ];

        $this->backupFinder->shouldReceive('find')
            ->with('install/backups', 'install', $config)
            ->andReturn($backups)
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with('backups/3')
            ->andReturnUsing(function () {
                // Free up some space
                $GLOBALS['disk_free_space'] += 104857600;
            })
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with('backups/2')
            ->andReturnUsing(function () {
                // Free up some space
                $GLOBALS['disk_free_space'] += 104857600;
            })
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with('backups/1')
            ->andReturnUsing(function () {
                // Free up some space
                $GLOBALS['disk_free_space'] += 104857600;
            })
            ->once();

        static::assertTrue($this->handler->handle(new CheckDiskSpace('install', 'install/backups'), $config));
    }

    public function testDoesNotRemoveMostRecentBackups()
    {
        $GLOBALS['disk_total_space'] = 1048576000;
        $GLOBALS['disk_free_space'] = 419430400;

        $config = ['name' => 'test'];

        $backups = [
            new Backup('backups/5', []),
            new Backup('backups/4', []),
        ];

        $this->backupFinder->shouldReceive('find')
            ->with('install/backups', 'install', $config)
            ->andReturn($backups)
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->never();

        static::assertFalse($this->handler->handle(new CheckDiskSpace('install', 'install/backups'), $config));
    }

    public function testRemovesOldBackupsWhenRunningLowOnSpaceButNotEnoughIsFreedUp()
    {
        $GLOBALS['disk_total_space'] = 1048576000;
        $GLOBALS['disk_free_space'] = 104857600;

        $config = ['name' => 'test'];

        $backups = [
            new Backup('backups/5', []),
            new Backup('backups/4', []),
            new Backup('backups/3', []),
            new Backup('backups/2', []),
            new Backup('backups/1', []),
        ];

        $this->backupFinder->shouldReceive('find')
            ->with('install/backups', 'install', $config)
            ->andReturn($backups)
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with('backups/3')
            ->andReturnUsing(function () {
                // Free up some space
                $GLOBALS['disk_free_space'] += 100;
            })
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with('backups/2')
            ->andReturnUsing(function () {
                // Free up some space
                $GLOBALS['disk_free_space'] += 100;
            })
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with('backups/1')
            ->andReturnUsing(function () {
                // Free up some space
                $GLOBALS['disk_free_space'] += 100;
            })
            ->once();

        static::assertFalse($this->handler->handle(new CheckDiskSpace('install', 'install/backups'), $config));
    }
}

function disk_total_space($directory)
{
    return $GLOBALS['disk_total_space'] ?? 1048576000;
}

function disk_free_space($directory)
{
    return $GLOBALS['disk_free_space'] ?? 1048576000;
}
