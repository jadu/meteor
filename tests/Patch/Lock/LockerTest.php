<?php

namespace Meteor\Patch\Lock;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class LockerTest extends TestCase
{
    private $locker;

    protected function setUp(): void
    {
        $this->locker = new Locker();

        vfsStream::setup('root');
    }

    public function testLock()
    {
        $this->locker->lock(vfsStream::url('root'));

        static::assertTrue(is_file(vfsStream::url('root/' . Locker::FILENAME)));
    }

    public function testLockThrowsExceptionIfUnableToCreateLockFile()
    {
        static::expectException(RuntimeException::class);

        $this->locker->lock(vfsStream::url('root'));
        $this->locker->lock(vfsStream::url('root'));
    }

    public function testUnlock()
    {
        $this->locker->lock(vfsStream::url('root'));
        static::assertTrue($this->locker->unlock(vfsStream::url('root')));

        static::assertFalse(is_file(vfsStream::url('root/' . Locker::FILENAME)));
    }

    public function testUnlockReturnsFalseIfNotLocked()
    {
        static::assertFalse($this->locker->unlock(vfsStream::url('root')));
    }
}
