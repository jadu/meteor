<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use Mockery;

class BackupFilesHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $filesystem;
    private $configurationLoader;
    private $io;
    private $handler;

    public function setUp()
    {
        $this->filesystem = Mockery::mock('Meteor\Filesystem\Filesystem', array(
            'ensureDirectoryExists' => null,
            'findFiles' => array(),
            'copyFiles' => null,
            'copy' => null,
        ));
        $this->configurationLoader = Mockery::mock('Meteor\Configuration\configurationLoader', array(
            'resolve' => null,
        ));
        $this->io = new NullIO();

        $this->handler = new BackupFilesHandler($this->filesystem, $this->configurationLoader, $this->io);
    }

    public function testCopiesFilesFromInstallIntoBackupDirectory()
    {
        $patchFiles = array('VERSION');
        $this->filesystem->shouldReceive('findFiles')
            ->with('patch/to_patch')
            ->andReturn($patchFiles)
            ->once();

        $this->filesystem->shouldReceive('copyFiles')
            ->with($patchFiles, 'install', 'install/backups/20160701000000/to_patch')
            ->once();

        $this->handler->handle(new BackupFiles('install/backups/20160701000000', 'patch', 'install'), array());
    }

    public function testCopiesMeteorConfigIntoBackupFromPatch()
    {
        $this->configurationLoader->shouldReceive('resolve')
            ->with('patch')
            ->andReturn('patch/meteor.json.package')
            ->once();

        $this->filesystem->shouldReceive('copy')
            ->with('patch/meteor.json.package', 'install/backups/20160701000000/meteor.json.package', true)
            ->once();

        $this->handler->handle(new BackupFiles('install/backups/20160701000000', 'patch', 'install'), array());
    }
}
