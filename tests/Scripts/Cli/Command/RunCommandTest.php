<?php

namespace Meteor\Scripts\Cli\Command;

use Meteor\Cli\Command\CommandTestCase;
use Meteor\IO\NullIO;
use Mockery;

class RunCommandTest extends CommandTestCase
{
    private $scriptRunner;

    public function createCommand()
    {
        $this->scriptRunner = Mockery::mock('Meteor\Scripts\ScriptRunner');

        return new RunCommand(null, array('name' => 'test'), new NullIO(), $this->scriptRunner);
    }

    public function testRunsCommand()
    {
        $workingDir = __DIR__;

        $this->scriptRunner->shouldReceive('setWorkingDir')
            ->with($workingDir)
            ->once();

        $this->scriptRunner->shouldReceive('run')
            ->with('test')
            ->andReturn(true)
            ->once();

        $this->tester->execute(array(
            'script' => 'test',
            '--working-dir' => $workingDir,
        ));
    }
}
