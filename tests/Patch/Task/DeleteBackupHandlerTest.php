<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use Mockery;

class DeleteBackupHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $io;
    private $filesystem;
    private $handler;

    public function setUp()
    {
        $this->io = new NullIO();
        $this->filesystem = Mockery::mock('Meteor\Filesystem\Filesystem', array(
            'findNewFiles' => array(),
            'copyDirectory' => null,
        ));

        $this->handler = new DeleteBackupHandler($this->io, $this->filesystem);
    }

    public function testDeletesBackup()
    {
        $this->filesystem->shouldReceive('remove')
            ->with('backups/1')
            ->once();

        $this->handler->handle(new DeleteBackup('backups/1'));
    }
}
