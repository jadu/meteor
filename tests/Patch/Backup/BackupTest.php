<?php

namespace Meteor\Patch\Backup;

class BackupTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDate()
    {
        $backup = new Backup('/path/to/20160203145217', []);

        $this->assertSame('2016-02-03T14:52:17+00:00', $backup->getDate()->format('c'));
    }
}
