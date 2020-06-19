<?php

namespace Meteor\Patch\Task;

use Meteor\Configuration\ConfigurationLoader;
use Meteor\Filesystem\Filesystem;
use Meteor\IO\NullIO;
use Mockery;
use PHPUnit_Framework_TestCase;

class BackupFilesHandlerTest extends PHPUnit_Framework_TestCase
{
    private $filesystem;
    private $configurationLoader;
    private $io;
    private $handler;
    private $config = [];

    public function setUp()
    {
        $this->config['patch']['replace_directories'] = [];
        $this->filesystem = Mockery::mock(Filesystem::class, [
            'ensureDirectoryExists' => null,
            'findFiles' => [],
            'copyFiles' => null,
            'copy' => null,
            'copyDirectory' => null,
        ]);
        $this->configurationLoader = Mockery::mock(ConfigurationLoader::class, [
            'resolve' => null,
        ]);
        $this->io = new NullIO();

        $this->handler = new BackupFilesHandler($this->filesystem, $this->configurationLoader, $this->io);
    }

    public function testCopiesFilesFromInstallIntoBackupDirectory()
    {
        $patchFiles = ['VERSION'];
        $this->filesystem->shouldReceive('findFiles')
            ->with('patch/to_patch', [])
            ->andReturn($patchFiles)
            ->once();

        $this->filesystem->shouldReceive('copyFiles')
            ->with($patchFiles, 'install', 'install/backups/20160701000000/to_patch')
            ->once();

        $this->handler->handle(new BackupFiles('install/backups/20160701000000', 'patch', 'install'), $this->config);
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

        $this->handler->handle(new BackupFiles('install/backups/20160701000000', 'patch', 'install'), $this->config);
    }

    public function testReplaceDirectoriesAreExcludedFromNormalBackupCopy()
    {
        $config = [];
        $config['patch']['replace_directories'] = [
            '/vendor',
        ];

        $this->filesystem->shouldReceive('findFiles')
            ->with('patch/to_patch', ['**', '!/vendor'])
            ->andReturn(['patch'])
            ->once();

        $this->filesystem->shouldReceive('findFiles')
            ->with('install', ['/vendor'])
            ->andReturn(['vendor'])
            ->once();

        $this->filesystem->shouldReceive('copyFiles')
            ->with(['patch', 'vendor'], 'install', 'install/backups/20160701000000/to_patch')
            ->andReturn([])
            ->once();

        $this->handler->handle(new BackupFiles('install/backups/20160701000000', 'patch', 'install'), $config);
    }
}
