<?php

namespace Meteor\Patch\Cli\Command;

use Meteor\Cli\Command\CommandTestCase;
use Meteor\IO\NullIO;
use Meteor\Patch\Lock\Locker;
use Mockery;

class ClearLockCommandTest extends CommandTestCase
{
    private $platform;
    private $locker;

    public function createCommand()
    {
        $this->platform = Mockery::mock('Meteor\Platform\PlatformInterface');
        $this->locker = Mockery::mock('Meteor\Patch\Lock\Locker');

        return new ClearLockCommand(null, [], new NullIO(), $this->platform, $this->locker);
    }

    public function testUnlocksInstall()
    {
        $installDir = __DIR__;

        $this->locker->shouldReceive('unlock')
            ->with($installDir)
            ->once();

        $this->tester->execute([
            '--install-dir' => $installDir,
        ]);
    }
}
