<?php

namespace Meteor\Patch\Cli\Command;

use Meteor\Cli\Command\CommandTestCase;
use Meteor\IO\NullIO;
use Meteor\Patch\Event\PatchEvents;
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

        $this->eventDispatcher->shouldReceive('dispatch')
            ->with(PatchEvents::PRE_APPLY, Mockery::any())
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

        $this->eventDispatcher->shouldReceive('dispatch')
            ->with(PatchEvents::POST_APPLY, Mockery::any())
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
     * @expectedExceptionMessage Your PHP version (5.6.0) is not sufficient enough for the package "test", which requires >=7
     */
    public function testPhpVersionConstraint()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = array(
            'name' => 'test',
            'package' => array(
                'php' => '>=7',
            ),
        );

        $this->command->setConfiguration($config);
        $this->command->setPhpVersion('5.6.0');

        $this->tester->execute(array(
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ));
    }

    /**
     * @expectedException Meteor\Patch\Exception\PhpVersionException
     * @expectedExceptionMessage Your PHP version (5.4.0) is not sufficient enough for the package "package/second", which requires >=5.6
     */
    public function testCombinedPhpVersionConstraint()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = array(
            'name' => 'test',
            'package' => array(
                'php' => '>=5.3.3',
            ),
            'combined' => array(
                array(
                    'name' => 'package/first',
                    'package' => array(
                        'php' => '>=5.4.0',
                    ),
                ),
                array(
                    'name' => 'package/second',
                    'package' => array(
                        'php' => '>=5.6',
                    ),
                ),
            ),
        );

        $this->command->setConfiguration($config);
        $this->command->setPhpVersion('5.4.0');

        $this->tester->execute(array(
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ));
    }

    public function testDoesNotRunScriptsIfSkipped()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = array('name' => 'test');
        $this->command->setConfiguration($config);

        $this->platform->shouldReceive('setInstallDir')
            ->with($installDir);

        $this->scriptRunner->shouldReceive('setWorkingDir')
            ->with($installDir);

        $this->logger->shouldReceive('enable');

        $this->locker->shouldReceive('lock')
            ->with($installDir);

        $this->eventDispatcher->shouldReceive('dispatch')
            ->with(PatchEvents::PRE_APPLY, Mockery::any())
            ->never();

        $tasks = array(
            new \stdClass(),
            new \stdClass(),
        );
        $this->strategy->shouldReceive('apply')
            ->with($workingDir, $installDir, Mockery::any())
            ->andReturn($tasks);

        $this->taskBus->shouldReceive('run')
            ->with($tasks[0], $config)
            ->andReturn(true);

        $this->taskBus->shouldReceive('run')
            ->with($tasks[1], $config)
            ->andReturn(true);

        $this->eventDispatcher->shouldReceive('dispatch')
            ->with(PatchEvents::POST_APPLY, Mockery::any())
            ->never();

        $this->locker->shouldReceive('unlock')
            ->with($installDir);

        $this->tester->execute(array(
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
            '--skip-scripts' => null,
        ));
    }
}
