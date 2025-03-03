<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use Meteor\Patch\Backup\Backup;
use Mockery;
use PHPUnit\Framework\TestCase;

class CheckDiskSpaceHandlerTest extends TestCase
{
    private const PATCH_SIZE_BYTES = 346030080;

    private $backupFinder;
    private $filesystem;
    private $io;
    private $handler;

    protected function setUp(): void
    {
        $this->backupFinder = Mockery::mock('Meteor\Patch\Backup\BackupFinder');
        $this->filesystem = Mockery::mock('Meteor\Filesystem\Filesystem');

        $this->filesystem->shouldReceive('getDirectorySize')
            ->with('/path/to/patch')
            ->andReturn(static::PATCH_SIZE_BYTES);

        $this->io = new NullIO();

        $this->handler = new CheckDiskSpaceHandler($this->backupFinder, $this->filesystem, $this->io);
    }

    public function testPlentyOfSpace()
    {
        $GLOBALS['disk_free_space'] = 1048576000;

        $config = ['name' => 'test'];

        $this->filesystem->shouldReceive('getDirectorySize')
            ->with('/path/to/patch')
            ->once()
            ->andReturn(static::PATCH_SIZE_BYTES);

        static::assertTrue($this->handler->handle(new CheckDiskSpace('install', 'install/backups', '/path/to/patch'), $config));
    }

    public function testWhenRunningLowOnSpace()
    {
        $GLOBALS['disk_free_space'] = 419430400;

        $config = ['name' => 'test'];

        $this->backupFinder->shouldReceive('find')
            ->with('install/backups', 'install', $config)
            ->andReturn([]);

        $this->filesystem->shouldReceive('getDirectorySize')
            ->with('/path/to/patch')
            ->once()
            ->andReturn(static::PATCH_SIZE_BYTES);

        static::assertFalse($this->handler->handle(new CheckDiskSpace('install', 'install/backups', '/path/to/patch'), $config));
    }

    public function testRemovesOldBackupsWhenRunningLowOnSpace()
    {
        $GLOBALS['disk_free_space'] = static::PATCH_SIZE_BYTES;

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
                $GLOBALS['disk_free_space'] += static::PATCH_SIZE_BYTES;
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

        static::assertTrue($this->handler->handle(new CheckDiskSpace('install', 'install/backups', '/path/to/patch'), $config));
    }

    public function testDoesNotRemoveMostRecentBackups()
    {
        $GLOBALS['disk_free_space'] = static::PATCH_SIZE_BYTES;

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

        static::assertFalse($this->handler->handle(new CheckDiskSpace('install', 'install/backups', '/path/to/patch'), $config));
    }

    public function testRemovesOldBackupsWhenRunningLowOnSpaceButNotEnoughIsFreedUp()
    {
        $GLOBALS['disk_free_space'] = 2000;

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

        static::assertFalse($this->handler->handle(new CheckDiskSpace('install', 'install/backups', '/path/to/patch'), $config));
    }

    public function testWarningOutputWhenNotEnoughSpace()
    {
        $GLOBALS['disk_free_space'] = 2000000;

        $config = ['name' => 'test'];

        $backups = [
            new Backup('backups/2', []),
            new Backup('backups/1', []),
        ];

        $this->backupFinder->shouldReceive('find')
            ->with('install/backups', 'install', $config)
            ->andReturn($backups)
            ->once();

        $io = Mockery::mock(\Meteor\IO\IOInterface::class, [
            'askConfirmation' => null,
            'debug' => null,
        ]);

        $this->handler = new CheckDiskSpaceHandler($this->backupFinder, $this->filesystem, $io);

        $io->shouldReceive('warning')
            ->once()
            ->with('There is not enough free disk space to apply this patch. Space required: 825.00 MB, Space available: 1.91 MB');

        static::assertFalse($this->handler->handle(new CheckDiskSpace('install', 'install/backups', '/path/to/patch'), $config));
    }
}

function disk_free_space($directory)
{
    return $GLOBALS['disk_free_space'] ?? 1048576000;
}
