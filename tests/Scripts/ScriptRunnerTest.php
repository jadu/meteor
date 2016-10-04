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

    public function testRunsGlobalScriptCommand()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), array(
            'global' => array(
                'test' => array('ls'),
            ),
        ));

        $this->processRunner->shouldReceive('run')
            ->with('ls', null)
            ->once();

        $scriptRunner->run('test');
    }

    public function testRunsCombinedScriptCommand()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), array(
            'combined' => array(
                'jadu/cms' => array(
                    'test' => array('ls'),
                ),
            ),
        ));

        $this->processRunner->shouldReceive('run')
            ->with('ls', null)
            ->once();

        $scriptRunner->run('test');
    }

    public function testRunsAllGlobalCommands()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), array(
            'global' => array(
                'test' => array('test1', 'test2'),
            ),
        ));

        $this->processRunner->shouldReceive('run')
            ->with('test1', null)
            ->once();

        $this->processRunner->shouldReceive('run')
            ->with('test2', null)
            ->once();

        $scriptRunner->run('test');
    }

    public function testRunsGlobalAndCombinedScriptCommands()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), array(
            'global' => array(
                'test' => array('test1', 'test2'),
            ),
            'combined' => array(
                'jadu/cms' => array(
                    'test' => array('test3'),
                ),
                'spacecraft/client' => array(
                    'test' => array('test4'),
                ),
            ),
        ));

        $this->processRunner->shouldReceive('run')
            ->with('test1', null)
            ->once();

        $this->processRunner->shouldReceive('run')
            ->with('test2', null)
            ->once();

        $this->processRunner->shouldReceive('run')
            ->with('test3', null)
            ->once();

        $this->processRunner->shouldReceive('run')
            ->with('test4', null)
            ->once();

        $scriptRunner->run('test');
    }

    public function testRunsGlobalScriptCommandWithWorkingDirectory()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), array(
            'global' => array(
                'test' => array('ls'),
            ),
        ));

        $this->processRunner->shouldReceive('run')
            ->with('ls', 'install')
            ->once();

        $scriptRunner->setWorkingDir('install');
        $scriptRunner->run('test');
    }

    public function testRunsGlobalReferencedScriptCommand()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), array(
            'global' => array(
                'test' => array('@clear-cache'),
                'clear-cache' => array('clear-cache.sh'),
            ),
        ));

        $this->processRunner->shouldReceive('run')
            ->with('clear-cache.sh', null)
            ->once();

        $scriptRunner->run('test');
    }

    public function testRunsCombinedReferencedScriptCommand()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), array(
            'combined' => array(
                'jadu/cms' => array(
                    'test' => array('@clear-cache'),
                    'clear-cache' => array('clear-cache.sh'),
                ),
            ),
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
    public function testPreventGlobalInfiniteLoop()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), array(
            'global' => array(
                'test' => array('@test'),
            ),
        ));

        $scriptRunner->run('test');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Infinite recursion
     */
    public function testPreventsCombinedInfiniteLoop()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), array(
            'combined' => array(
                'jadu/cms' => array(
                    'test' => array('@test'),
                ),
            ),
        ));

        $scriptRunner->run('test');
    }

}
