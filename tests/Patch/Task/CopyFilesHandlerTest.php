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
    private $config = [];

    public function setUp()
    {
        $this->config['patch']['swap_folders'] = [];
        $this->io = new NullIO();
        $this->filesystem = Mockery::mock(Filesystem::class, [
            'findNewFiles' => [],
            'copyDirectory' => null,
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

        $this->handler->handle(new CopyFiles('source', 'target'), $this->config);
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

        $this->handler->handle(new CopyFiles('source', 'target'), $this->config);
    }

    public function testHandlesSwapFoldersConfig()
    {
        $config = [];
        $config['patch']['swap_folders'] = [
            '/vendor',
        ];

        $newFiles = [
            'test',
        ];

        $this->filesystem->shouldReceive('findNewFiles')
            ->with('source', 'target', ['!/vendor'])
            ->andReturn($newFiles)
            ->once();

         $this->filesystem->shouldReceive('swapDirectory')
            ->with('source', 'target', '/vendor')
            ->once();

        $this->permissionSetter->shouldReceive('setDefaultPermissions')
            ->with($newFiles, 'target')
            ->ordered()
            ->once();

        $this->handler->handle(new CopyFiles('source', 'target'), $config);
    }
}
