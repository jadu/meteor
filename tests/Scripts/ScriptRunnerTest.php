<?php

namespace Meteor\Scripts;

use Meteor\IO\NullIO;
use Meteor\Process\ProcessRunner;
use Mockery;

class ScriptRunnerTest extends \PHPUnit_Framework_TestCase
{
    private $processRunner;

    public function setUp()
    {
        $this->processRunner = Mockery::mock('Meteor\Process\ProcessRunner');
    }

    public function testRunHandlesUnknownScriptsGracefully()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), array());
        $scriptRunner->run('test');
    }

    public function testRunsScriptCommand()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), array(
            'test' => array('ls'),
        ));

        $this->processRunner->shouldReceive('run')
            ->with('ls', null)
            ->once();

        $scriptRunner->run('test');
    }

    public function testRunsRunsAllCommands()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), array(
            'test' => array('test1', 'test2'),
        ));

        $this->processRunner->shouldReceive('run')
            ->with('test1', null)
            ->once();

        $this->processRunner->shouldReceive('run')
            ->with('test2', null)
            ->once();

        $scriptRunner->run('test');
    }

    public function testRunsScriptCommandWithWorkingDirectory()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), array(
            'test' => array('ls'),
        ));

        $this->processRunner->shouldReceive('run')
            ->with('ls', 'install')
            ->once();

        $scriptRunner->setWorkingDir('install');
        $scriptRunner->run('test');
    }

    public function testRunsReferencedScriptCommand()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), array(
            'test' => array('@clear-cache'),
            'clear-cache' => array('clear-cache.sh'),
        ));

        $this->processRunner->shouldReceive('run')
            ->with('clear-cache.sh', null)
            ->once();

        $scriptRunner->run('test');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Infinite recursion
     */
    public function testPreventInfiniteLoop()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), array(
            'test' => array('@test'),
        ));

        $scriptRunner->run('test');
    }
}
