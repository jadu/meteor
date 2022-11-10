<?php

namespace Meteor\Patch\Task;

use Meteor\Filesystem\Filesystem;
use Meteor\IO\NullIO;
use Meteor\Patch\Backup\Backup;
use Meteor\Patch\Backup\BackupFinder;
use Mockery;
use PHPUnit\Framework\TestCase;

class LimitBackupsHandlerTest extends TestCase
{
    protected $backupFinder;

    protected $filesystem;

    protected $io;

    protected $handler;

    protected function setUp(): void
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

        $this->filesystem->shouldReceive('ensureDirectoryExists')
            ->with('backups')
            ->andReturn(true);

        $this->backupFinder->shouldReceive('find')
            ->with('backups', 'install', $config)
            ->andReturn($backups)
            ->once();

        static::assertTrue($this->handler->handle(new LimitBackups('backups', 'install', 10), $config));
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

        $this->filesystem->shouldReceive('ensureDirectoryExists')
            ->with('backups')
            ->andReturn(true);

        $this->backupFinder->shouldReceive('find')
            ->with('backups', 'install', $config)
            ->andReturn($backups)
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with('backups/2')
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with('backups/1')
            ->once();

        static::assertTrue($this->handler->handle(new LimitBackups('backups', 'install', 3), $config));
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

        $this->filesystem->shouldReceive('ensureDirectoryExists')
            ->with('backups')
            ->andReturn(true);

        $this->backupFinder->shouldReceive('find')
            ->with('backups', 'install', $config)
            ->andReturn($backups)
            ->once();

        static::assertTrue($this->handler->handle(new LimitBackups('backups', 'install', 0), $config));
    }
}
