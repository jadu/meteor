<?php

namespace Meteor\Patch\Backup;

use PHPUnit\Framework\TestCase;

class BackupTest extends TestCase
{
    public function testGetDate()
    {
        $backup = new Backup('/path/to/20160203145217', []);

        static::assertSame('2016-02-03T14:52:17+00:00', $backup->getDate()->format('c'));
    }
}
