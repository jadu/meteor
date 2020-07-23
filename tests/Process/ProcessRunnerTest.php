<?php

namespace Meteor\Process;

use Meteor\IO\IOInterface;
use Mockery;
use Mockery\Mock;
use Symfony\Component\Process\Process;

class ProcessRunnerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessRunner
     */
    private $processRunner;

    /**
     * @var MemoryLimitSetter|Mock
     */
    private $memoryLimitSetter;

    /**
     * @var IOInterface|Mock
     */
    private $io;

    /**
     * @var ProcessFactory|Mock
     */
    private $processFactory;

    /**
     * @var Process|Mock
     */
    private $process;

    public function setUp()
    {
        $this->memoryLimitSetter = Mockery::mock(MemoryLimitSetter::class, [
            'isPHPScript' => false
        ]);
        $this->io = Mockery::mock(IOInterface::class, [
            'debug' => null
        ]);
        $this->process = Mockery::mock(Process::class, [
            'setWorkingDirectory' => null,
            'stop' => null,
            'setTimeout' => null,
            'run' => null,
            'getOutput' => '',
            'isSuccessful' => true
        ]);
        $this->processFactory = Mockery::mock(ProcessFactory::class, [
            'create' => $this->process
        ]);

        $this->processRunner = new ProcessRunner(
            $this->io,
            $this->memoryLimitSetter,
            $this->processFactory
        );
    }

    public function testRunReturnsOutputWhenSuccessful()
    {
        $this->process->shouldReceive('getOutput')
            ->andReturn('done');

        self::assertSame('done', $this->processRunner->run('whoami'));
    }

    public function testRunThrowsExceptionWithErrorOutputWhenNotSuccessful()
    {
        $this->process->shouldReceive('isSuccessful')
            ->andReturn(false);
        $this->process->shouldReceive('getErrorOutput')
            ->andReturn('error');

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('error');

        $this->processRunner->run('invalidcommand');
    }

    public function testSetMemoryLimitOnPHPScript()
    {
        $command = 'php cli.php cache:warmup --kernel=frontend';
        $commandWithLimit = 'php -dmemory_limit=-1 cli.php cache:warmup --kernel=frontend';

        $this->memoryLimitSetter->shouldReceive('isPHPScript')
            ->with($command)
            ->andReturn(true);
        $this->memoryLimitSetter->shouldReceive('hasMemoryLimit')
            ->with($command)
            ->andReturn(false);
        $this->memoryLimitSetter->shouldReceive('setMemoryLimit')
            ->with($command)
            ->andReturn($commandWithLimit);

        $this->processFactory->shouldReceive('create')
            ->with($commandWithLimit)
            ->andReturn($this->process);

        $this->processRunner->run($command);
    }

    public function testDoesSetMemoryLimitOnPHPScriptWhenAlreadyDefined()
    {
        $command = 'php -dmemory_limit=-1 cli.php cache:warmup --kernel=frontend';

        $this->memoryLimitSetter->shouldReceive('isPHPScript')
            ->with($command)
            ->andReturn(true);
        $this->memoryLimitSetter->shouldReceive('hasMemoryLimit')
            ->with($command)
            ->andReturn(true);

        $this->processFactory->shouldReceive('create')
            ->with($command)
            ->andReturn($this->process);

        $this->processRunner->run($command);
    }

    public function testDoesNotSetLimitIfNotPHPScript()
    {
        $command = '/bin/bash foo.bar';

        $this->memoryLimitSetter->shouldReceive('isPHPScript')
            ->with($command)
            ->andReturn(false);

        $this->processFactory->shouldReceive('create')
            ->with($command)
            ->andReturn($this->process);

        $this->processRunner->run($command);
    }

    public function testSetsWorkingDirectory()
    {
        $command = 'php cli.php cache:warmup --kernel=frontend';

        $this->process->shouldReceive('setWorkingDirectory')
            ->with('/usr/mikes')
            ->once();

        $this->processRunner->run($command, '/usr/mikes');
    }

    public function testSetsTimeout()
    {
        $command = 'php cli.php cache:warmup --kernel=frontend';

        $this->process->shouldReceive('setTimeout')
            ->with(666)
            ->once();

        $this->processRunner->run($command, '/usr/mikes', null, 666);
    }

    public function testCallbackIsRun()
    {
        $command = 'php cli.php cache:warmup --kernel=frontend';
        $callback = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $this->process->shouldReceive('run')
            ->with($callback)
            ->once();

        $this->processRunner->run($command, '/usr/mikes', $callback);
    }
}
