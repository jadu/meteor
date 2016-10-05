<?php

namespace Meteor\Patch\Cli\Command;

use Meteor\Cli\Command\CommandTestCase;
use Meteor\IO\NullIO;
use Mockery;
use org\bovigo\vfs\vfsStream;

class ApplyCommandTest extends CommandTestCase
{
    private $taskBus;
    private $strategy;
    private $platform;
    private $locker;
    private $eventDispatcher;
    private $scriptRunner;
    private $logger;

    public function createCommand()
    {
        vfsStream::setup('root', null, array(
            'patch' => array(),
            'install' => array(),
        ));

        $this->taskBus = Mockery::mock('Meteor\Patch\Task\TaskBusInterface');
        $this->strategy = Mockery::mock('Meteor\Patch\Strategy\PatchStrategyInterface');
        $this->platform = Mockery::mock('Meteor\Platform\PlatformInterface', array(
            'setInstallDir' => null,
        ));
        $this->locker = Mockery::mock('Meteor\Patch\Lock\Locker');
        $this->eventDispatcher = Mockery::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface', array(
            'dispatch' => null,
        ));
        $this->scriptRunner = Mockery::mock('Meteor\Scripts\ScriptRunner', array(
            'setWorkingDir' => null,
        ));
        $this->logger = Mockery::mock('Meteor\Logger\LoggerInterface');

        $this->strategy->shouldReceive('configureApplyCommand')
            ->once();

        return new ApplyCommand(
            null,
            array(),
            new NullIO(),
            $this->platform,
            $this->taskBus,
            $this->strategy,
            $this->locker,
            $this->eventDispatcher,
            $this->scriptRunner,
            $this->logger
        );
    }

    public function testAppliesPatch()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = array('name' => 'test');
        $this->command->setConfiguration($config);

        $this->platform->shouldReceive('setInstallDir')
            ->with($installDir)
            ->once();

        $this->scriptRunner->shouldReceive('setWorkingDir')
            ->with($installDir)
            ->once();

        $this->logger->shouldReceive('enable')
            ->once();

        $this->locker->shouldReceive('lock')
            ->with($installDir)
            ->once();

        $tasks = array(
            new \stdClass(),
            new \stdClass(),
        );
        $this->strategy->shouldReceive('apply')
            ->with($workingDir, $installDir, Mockery::any())
            ->andReturn($tasks)
            ->once();

        $this->taskBus->shouldReceive('run')
            ->with($tasks[0], $config)
            ->andReturn(true)
            ->once();

        $this->taskBus->shouldReceive('run')
            ->with($tasks[1], $config)
            ->andReturn(true)
            ->once();

        $this->locker->shouldReceive('unlock')
            ->with($installDir)
            ->once();

        $this->tester->execute(array(
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testThrowsExceptionWhenWorkingDirIsTheSameAsTheInstallDir()
    {
        $workingDir = vfsStream::url('root/install');
        $installDir = vfsStream::url('root/install');

        $this->tester->execute(array(
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ));
    }

    public function testDoesNotLockWhenSkipLockOptionSpecified()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = array('name' => 'test');
        $this->command->setConfiguration($config);

        $this->logger->shouldReceive('enable')
            ->once();

        $this->locker->shouldReceive('lock')
            ->never();

        $tasks = array(new \stdClass());
        $this->strategy->shouldReceive('apply')
            ->with($workingDir, $installDir, Mockery::any())
            ->andReturn($tasks)
            ->once();

        $this->taskBus->shouldReceive('run')
            ->with($tasks[0], $config)
            ->andReturn(true)
            ->once();

        $this->locker->shouldReceive('unlock')
            ->never();

        $this->tester->execute(array(
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
            '--skip-lock' => null,
        ));
    }

    public function testDoesNotUnlockIfTaskFails()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = array('name' => 'test');
        $this->command->setConfiguration($config);

        $this->logger->shouldReceive('enable')
            ->once();

        $this->locker->shouldReceive('lock')
            ->with($installDir)
            ->once();

        $tasks = array(new \stdClass());
        $this->strategy->shouldReceive('apply')
            ->with($workingDir, $installDir, Mockery::any())
            ->andReturn($tasks)
            ->once();

        $this->taskBus->shouldReceive('run')
            ->with($tasks[0], $config)
            ->andReturn(false)
            ->once();

        $this->locker->shouldReceive('unlock')
            ->never();

        $this->tester->execute(array(
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ));
    }

    /**
     * @expectedException Meteor\Patch\Exception\PhpVersionException
     */
    public function testUnsatisfiedPhpVersionConstraint()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = array(
            'name' => 'test',
            'package' => array(
                'php' => '>=7'
            )
        );

        $this->command->setConfiguration($config);
        $this->command->setPhpVersion('5.6.0');

        $this->tester->execute(array(
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ));
    }
}
