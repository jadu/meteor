<?php

namespace Meteor\Scripts;

use Meteor\IO\NullIO;
use Mockery;
use PHPUnit\Framework\TestCase;

class ScriptRunnerTest extends TestCase
{
    private $processRunner;

    protected function setUp(): void
    {
        $this->processRunner = Mockery::mock('Meteor\Process\ProcessRunner');
    }

    public function testRunHandlesUnknownScriptsGracefully()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), []);
        $scriptRunner->run('test');
    }

    public function testRunsCustomerScriptCommand()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), [
            'spacecraft/customer' => [
                'test' => ['ls'],
            ],
        ]);

        $this->processRunner->shouldReceive('run')
            ->with('ls', null)
            ->once();

        $scriptRunner->run('test');
    }

    public function testRunsCombinedScriptCommand()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), [
            'jadu/cms' => [
                'test' => ['ls'],
            ],
        ]);

        $this->processRunner->shouldReceive('run')
            ->with('ls', null)
            ->once();

        $scriptRunner->run('test');
    }

    public function testRunsAllCustomerCommands()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), [
            'spacecraft/customer' => [
                'test' => ['test1', 'test2'],
            ],
        ]);

        $this->processRunner->shouldReceive('run')
            ->with('test1', null)
            ->once();

        $this->processRunner->shouldReceive('run')
            ->with('test2', null)
            ->once();

        $scriptRunner->run('test');
    }

    public function testRunsCustomerAndCombinedScriptCommands()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), [
            'spacecraft/customer' => [
                'test' => ['test1', 'test2'],
            ],
            'jadu/cms' => [
                'test' => ['test3'],
            ],
            'spacecraft/client' => [
                'test' => ['test4'],
            ],
        ]);

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

    public function testRunsCustomerScriptCommandWithWorkingDirectory()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), [
            'spacecraft/customer' => [
                'test' => ['ls'],
            ],
        ]);

        $this->processRunner->shouldReceive('run')
            ->with('ls', 'install')
            ->once();

        $scriptRunner->setWorkingDir('install');
        $scriptRunner->run('test');
    }

    public function testRunsCustomerReferencedScriptCommand()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), [
            'spacecraft/customer' => [
                'test' => ['@clear-cache'],
                'clear-cache' => ['clear-cache.sh'],
            ],
        ]);

        $this->processRunner->shouldReceive('run')
            ->with('clear-cache.sh', null)
            ->once();

        $scriptRunner->run('test');
    }

    public function testRunsCombinedReferencedScriptCommand()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), [
            'jadu/cms' => [
                'test' => ['@clear-cache'],
                'clear-cache' => ['clear-cache.sh'],
            ],
        ]);

        $this->processRunner->shouldReceive('run')
            ->with('clear-cache.sh', null)
            ->once();

        $scriptRunner->run('test');
    }

    public function testRunsCombinedReferencedScriptWithMultipleCommands()
    {
        $scriptRunner = new ScriptRunner($this->processRunner, new NullIO(), [
            'jadu/cms' => [
                'test' => ['@clear-cache', '@warm-cache'],
                'clear-cache' => ['clear-cache.sh'],
                'warm-cache' => ['warm-cache.sh'],
            ],
        ]);

        $this->processRunner->shouldReceive('run')
            ->with('clear-cache.sh', null)
            ->once();

        $this->processRunner->shouldReceive('run')
            ->with('warm-cache.sh', null)
            ->once();

        $scriptRunner->run('test');
    }

    public function testRunUpdatesProgressBar()
    {
        $mockIO = Mockery::mock(\Meteor\IO\IOInterface::class, [
            'text' => null,
            'debug' => null,
            'newLine' => null,
        ]);

        $mockIO->shouldReceive('progressStart')
            ->once()
            ->with(2);

        $mockIO->shouldReceive('progressAdvance')
            ->twice();

        $mockIO->shouldReceive('progressFinish')
            ->once();

        $this->processRunner->shouldReceive('run')
            ->withAnyArgs()
            ->twice();

        $scriptRunner = new ScriptRunner($this->processRunner, $mockIO, [
            'jadu/cms' => [
                'test' => ['@clear-cache', '@warm-cache'],
                'clear-cache' => ['clear-cache.sh'],
                'warm-cache' => ['warm-cache.sh'],
            ],
        ]);

        $scriptRunner->run('test');
    }
}
