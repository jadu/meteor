<?php

namespace Meteor\Patch\Task;

use Meteor\Filesystem\Filesystem;
use Meteor\IO\NullIO;
use Meteor\Permissions\PermissionSetter;
use Mockery;
use PHPUnit_Framework_TestCase;

class CopyFilesHandlerTest extends PHPUnit_Framework_TestCase
{
    private $io;
    private $filesystem;
    private $permissionSetter;
    private $handler;
    private $defaultConfig = [];
    private $swapFoldersConfig = [];

    public function setUp()
    {
        $this->defaultConfig['patch']['swap_folders'] = [];
        $this->swapFoldersConfig['patch']['swap_folders'] = [
            '/vendor',
        ];
        $this->io = new NullIO();
        $this->filesystem = Mockery::mock(Filesystem::class, [
            'findNewFiles' => [],
            'copyDirectory' => null,
            'swapDirectory' => null,
        ]);
        $this->permissionSetter = Mockery::mock(PermissionSetter::class, [
            'setDefaultPermissions' => null,
        ]);
        $this->handler = new CopyFilesHandler($this->io, $this->filesystem, $this->permissionSetter);
    }

    public function testCopiesFiles()
    {
        $this->filesystem->shouldReceive('copyDirectory')
            ->with('source', 'target', [])
            ->once();

        $this->handler->handle(new CopyFiles('source', 'target'), $this->defaultConfig);
    }

    public function testSetsPermissions()
    {
        $newFiles = [
            'test',
        ];

        $this->filesystem->shouldReceive('findNewFiles')
            ->with('source', 'target', [])
            ->andReturn($newFiles)
            ->once();

        $this->permissionSetter->shouldReceive('setDefaultPermissions')
            ->with($newFiles, 'target')
            ->ordered()
            ->once();

        $this->handler->handle(new CopyFiles('source', 'target'), $this->defaultConfig);
    }

    public function testExcludesSwapFoldersFromCopyDirectory()
    {
         $this->filesystem->shouldReceive('copyDirectory')
            ->with('source', 'target', ['!/vendor'])
            ->once();

        $this->handler->handle(new CopyFiles('source', 'target'), $this->swapFoldersConfig);
    }

    public function testExcludesSwapFoldersFromFindNewFiles()
    {
         $this->filesystem->shouldReceive('findNewFiles')
            ->with('source', 'target', ['!/vendor'])
            ->andReturn([])
            ->once();

        $this->handler->handle(new CopyFiles('source', 'target'), $this->swapFoldersConfig);
    }

    public function testProcessesSwapFolders()
    {
        $this->filesystem->shouldReceive('swapDirectory')
            ->with('source', 'target', '/vendor')
            ->once();

        $this->handler->handle(new CopyFiles('source', 'target'), $this->swapFoldersConfig);
    }
}
