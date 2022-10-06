<?php

namespace Meteor\Patch\Task;

use Meteor\Configuration\ConfigurationLoader;
use Meteor\Filesystem\Filesystem;
use Meteor\IO\NullIO;
use Mockery;
use PHPUnit\Framework\TestCase;

class BackupFilesHandlerTest extends TestCase
{
    private $filesystem;
    private $configurationLoader;
    private $io;
    private $handler;
    private $config = [];

    protected function setUp(): void
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
            'resolve' => 'composer.json',
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
        $config['patch']['replace_directories'] = ['/forward', '\\backward', 'noward'];

        $this->filesystem->shouldReceive('findFiles')
            ->with('patch/to_patch', ['**', '!/forward', '!/backward', '!/noward'])
            ->andReturn(['patch'])
            ->once();
        $this->filesystem->shouldReceive('findFiles')
            ->with('install/forward')
            ->andReturn(['bar'])
            ->once();
        $this->filesystem->shouldReceive('findFiles')
            ->with('install/backward')
            ->andReturn(['bar'])
            ->once();
        $this->filesystem->shouldReceive('findFiles')
            ->with('install/noward')
            ->andReturn(['bar'])
            ->once();

        $this->filesystem->shouldReceive('copyFiles')
            ->with(['patch', 'forward', 'forward/bar', 'backward', 'backward/bar', 'noward', 'noward/bar'], 'install', 'install/backups/20160701000000/to_patch')
            ->andReturn([])
            ->once();

        $this->handler->handle(new BackupFiles('install/backups/20160701000000', 'patch', 'install'), $config);
    }

    public function testReplaceDirectoriesAreExcludedFromNormalBackupCopyWhenInCombinedConfig()
    {
        $config = [
            'patch' => [
                'replace_directories' => ['/vendor']
            ],
            'combined' => [
                [
                    'patch' => [
                        'replace_directories' => ['/foo']
                    ]
                ]
            ]
        ];

        $this->filesystem->shouldReceive('findFiles')
            ->with('patch/to_patch', ['**', '!/vendor', '!/foo'])
            ->andReturn(['patch'])
            ->once();
        $this->filesystem->shouldReceive('findFiles')
            ->with('install/vendor')
            ->andReturn(['bar'])
            ->once();
        $this->filesystem->shouldReceive('findFiles')
            ->with('install/foo')
            ->andReturn(['bar'])
            ->once();
        $this->filesystem->shouldReceive('copyFiles')
            ->with(['patch', 'vendor', 'vendor/bar', 'foo', 'foo/bar'], 'install', 'install/backups/20160701000000/to_patch')
            ->andReturn([])
            ->once();

        $this->handler->handle(new BackupFiles('install/backups/20160701000000', 'patch', 'install'), $config);
    }
}
