<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use Meteor\Permissions\PermissionSetter;
use Mockery;

class CopyFilesHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $io;
    private $filesystem;
    private $permissionSetter;
    private $handler;

    public function setUp()
    {
        $this->io = new NullIO();
        $this->filesystem = Mockery::mock('Meteor\Filesystem\Filesystem', array(
            'findNewFiles' => array(),
            'copyDirectory' => null,
        ));
        $this->permissionSetter = Mockery::mock('Meteor\Permissions\PermissionSetter', array(
            'setDefaultPermissions' => null,
            'setPermissions' => null,
        ));
        $this->handler = new CopyFilesHandler($this->io, $this->filesystem, $this->permissionSetter);
    }

    public function testCopiesFiles()
    {
        $this->filesystem->shouldReceive('copyDirectory')
            ->with('source', 'target')
            ->once();

        $this->handler->handle(new CopyFiles('source', 'target'), array());
    }

    public function testSetsPermissions()
    {
        $newFiles = array(
            'test',
        );

        $this->filesystem->shouldReceive('findNewFiles')
            ->with('source', 'target')
            ->andReturn($newFiles)
            ->once();

        $this->permissionSetter->shouldReceive('setDefaultPermissions')
            ->with($newFiles, 'target')
            ->ordered()
            ->once();

        $this->permissionSetter->shouldReceive('setPermissions')
            ->with('source', 'target')
            ->ordered()
            ->once();

        $this->handler->handle(new CopyFiles('source', 'target'), array());
    }
}
