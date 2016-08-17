<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use Meteor\Patch\Backup\Backup;
use Mockery;

class CheckDiskSpaceHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $backupFinder;
    private $filesystem;
    private $io;
    private $handler;

    public function setUp()
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

        $config = array('name' => 'test');
        $this->assertTrue($this->handler->handle(new CheckDiskSpace('install'), $config));
    }

    public function testWhenRunningLowOnSpace()
    {
        $GLOBALS['disk_total_space'] = 1048576000;
        $GLOBALS['disk_free_space'] = 419430400;

        $config = array('name' => 'test');

        $this->backupFinder->shouldReceive('find')
            ->with('install', $config)
            ->andReturn(array());

        $this->assertFalse($this->handler->handle(new CheckDiskSpace('install'), $config));
    }

    public function testRemovesOldBackupsWhenRunningLowOnSpace()
    {
        $GLOBALS['disk_total_space'] = 1048576000;
        $GLOBALS['disk_free_space'] = 419430400;

        $config = array('name' => 'test');

        $backups = array(
            new Backup('backups/1', array()),
            new Backup('backups/2', array()),
            new Backup('backups/3', array()),
            new Backup('backups/4', array()),
            new Backup('backups/5', array()),
        );

        $this->backupFinder->shouldReceive('find')
            ->with('install', $config)
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
            ->with('backups/4')
            ->andReturnUsing(function () {
                // Free up some space
                $GLOBALS['disk_free_space'] += 104857600;
            })
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with('backups/5')
            ->andReturnUsing(function () {
                // Free up some space
                $GLOBALS['disk_free_space'] += 104857600;
            })
            ->once();

        $this->assertTrue($this->handler->handle(new CheckDiskSpace('install'), $config));
    }

    public function testRemovesOldBackupsWhenRunningLowOnSpaceButNotEnoughIsFreedUp()
    {
        $GLOBALS['disk_total_space'] = 1048576000;
        $GLOBALS['disk_free_space'] = 104857600;

        $config = array('name' => 'test');

        $backups = array(
            new Backup('backups/1', array()),
            new Backup('backups/2', array()),
            new Backup('backups/3', array()),
            new Backup('backups/4', array()),
            new Backup('backups/5', array()),
        );

        $this->backupFinder->shouldReceive('find')
            ->with('install', $config)
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
            ->with('backups/4')
            ->andReturnUsing(function () {
                // Free up some space
                $GLOBALS['disk_free_space'] += 100;
            })
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with('backups/5')
            ->andReturnUsing(function () {
                // Free up some space
                $GLOBALS['disk_free_space'] += 100;
            })
            ->once();

        $this->assertFalse($this->handler->handle(new CheckDiskSpace('install'), $config));
    }
}

function disk_total_space($directory)
{
    return isset($GLOBALS['disk_total_space']) ? $GLOBALS['disk_total_space'] : 1048576000;
}

function disk_free_space($directory)
{
    return isset($GLOBALS['disk_free_space']) ? $GLOBALS['disk_free_space'] : 1048576000;
}
