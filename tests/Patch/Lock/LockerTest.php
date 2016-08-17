<?php

namespace Meteor\Patch\Lock;

use org\bovigo\vfs\vfsStream;

class LockerTest extends \PHPUnit_Framework_TestCase
{
    private $locker;

    public function setUp()
    {
        $this->locker = new Locker();

        vfsStream::setup('root');
    }

    public function testLock()
    {
        $this->locker->lock(vfsStream::url('root'));

        $this->assertTrue(is_file(vfsStream::url('root/'.Locker::FILENAME)));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testLockThrowsExceptionIfUnableToCreateLockFile()
    {
        $this->locker->lock(vfsStream::url('root'));
        $this->locker->lock(vfsStream::url('root'));
    }

    public function testUnlock()
    {
        $this->locker->lock(vfsStream::url('root'));
        $this->assertTrue($this->locker->unlock(vfsStream::url('root')));

        $this->assertFalse(is_file(vfsStream::url('root/'.Locker::FILENAME)));
    }

    public function testUnlockReturnsFalseIfNotLocked()
    {
        $this->assertFalse($this->locker->unlock(vfsStream::url('root')));
    }
}
