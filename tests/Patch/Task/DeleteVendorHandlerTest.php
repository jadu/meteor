<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use Mockery;

class DeleteVendorHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $io;
    private $filesystem;
    private $handler;

    public function setUp()
    {
        $this->io = new NullIO();
        $this->filesystem = Mockery::mock('Meteor\Filesystem\Filesystem', [
            'findNewFiles' => [],
            'copyDirectory' => null,
        ]);

        $this->handler = new DeleteVendorHandler($this->io, $this->filesystem);
    }

    public function testDeletesBackup()
    {
        $this->filesystem->shouldReceive('remove')
            ->with('/a/b/c/vendor')
            ->once();

        $this->handler->handle(new DeleteVendor('/a/b/c'));
    }
}
