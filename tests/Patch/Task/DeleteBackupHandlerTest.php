<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use Mockery;
use PHPUnit\Framework\TestCase;

class DeleteBackupHandlerTest extends TestCase
{
    private $io;
    private $filesystem;
    private $handler;

    protected function setUp(): void
    {
        $this->io = new NullIO();
        $this->filesystem = Mockery::mock('Meteor\Filesystem\Filesystem', [
            'findNewFiles' => [],
            'copyDirectory' => null,
        ]);

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
