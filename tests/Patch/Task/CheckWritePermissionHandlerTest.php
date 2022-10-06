<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class CheckWritePermissionHandlerTest extends TestCase
{
    private $handler;

    protected function setUp(): void
    {
        $this->handler = new CheckWritePermissionHandler(new NullIO());
    }

    public function testReturnsTrueWhenHasPermission()
    {
        vfsStream::setup('root');

        $this->assertTrue($this->handler->handle(new CheckWritePermission(vfsStream::url('root'))));
    }

    public function testReturnsFalseWhenDoesNotHavePermission()
    {
        vfsStream::setup('root', 0000);

        $this->assertFalse($this->handler->handle(new CheckWritePermission(vfsStream::url('root'))));
    }

    public function testReturnsFalseWhenDirectoryDoesNotExist()
    {
        $this->assertFalse($this->handler->handle(new CheckWritePermission(vfsStream::url('root'))));
    }
}
