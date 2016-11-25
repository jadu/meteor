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
        vfsStream::setup('root', null, [
            'patch' => [],
            'install' => [],
        ]);

        $this->taskBus = Mockery::mock('Meteor\Patch\Task\TaskBusInterface');
        $this->strategy = Mockery::mock('Meteor\Patch\Strategy\PatchStrategyInterface');
        $this->platform = Mockery::mock('Meteor\Platform\PlatformInterface', [
            'setInstallDir' => null,
        ]);
        $this->locker = Mockery::mock('Meteor\Patch\Lock\Locker');
        $this->eventDispatcher = Mockery::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface', [
            'dispatch' => null,
        ]);
        $this->scriptRunner = Mockery::mock('Meteor\Scripts\ScriptRunner', [
            'setWorkingDir' => null,
        ]);
        $this->logger = Mockery::mock('Meteor\Logger\LoggerInterface');

        $this->strategy->shouldReceive('configureApplyCommand')
            ->once();

        return new ApplyCommand(
            null,
            [],
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

        $config = ['name' => 'test'];
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

        $tasks = [
            new \stdClass(),
            new \stdClass(),
        ];
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

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testThrowsExceptionWhenWorkingDirIsTheSameAsTheInstallDir()
    {
        $workingDir = vfsStream::url('root/install');
        $installDir = vfsStream::url('root/install');

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ]);
    }

    public function testDoesNotLockWhenSkipLockOptionSpecified()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = ['name' => 'test'];
        $this->command->setConfiguration($config);

        $this->logger->shouldReceive('enable')
            ->once();

        $this->locker->shouldReceive('lock')
            ->never();

        $tasks = [new \stdClass()];
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

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
            '--skip-lock' => null,
        ]);
    }

    public function testDoesNotUnlockIfTaskFails()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = ['name' => 'test'];
        $this->command->setConfiguration($config);

        $this->logger->shouldReceive('enable')
            ->once();

        $this->locker->shouldReceive('lock')
            ->with($installDir)
            ->once();

        $tasks = [new \stdClass()];
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

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ]);
    }

    /**
     * @expectedException Meteor\Patch\Exception\PhpVersionException
     * @expectedExceptionMessage Your PHP version (5.6.0) is not sufficient enough for the package "test", which requires >=7
     */
    public function testPhpVersionConstraint()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = [
            'name' => 'test',
            'package' => [
                'php' => '>=7',
            ],
        ];

        $this->command->setConfiguration($config);
        $this->command->setPhpVersion('5.6.0');

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ]);
    }

    public function testAppliesPatchWithPhpVersionMetadata()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = [
            'name' => 'test',
            'package' => [
                "php" => ">=5.3.2"
            ],
        ];
        $this->command->setConfiguration($config);
        $this->command->setPhpVersion('5.6.0-1ubuntu3.25');

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

        $tasks = [
            new \stdClass(),
            new \stdClass(),
        ];
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

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ]);
    }

    public function testPhpVersionConstraintWithVersionMetadata()
    {
        $config = [
            'name' => 'test',
            'package' => [
                "php" => ">=5.3.2"
            ],
        ];

        $this->command->setConfiguration($config);
        $this->command->setPhpVersion('5.6.0-1ubuntu3.25');
        
        $this->assertEquals('5.6.0', $this->command->getPhpVersion());
    }

    /**
     * @expectedException Meteor\Patch\Exception\PhpVersionException
     * @expectedExceptionMessage Your PHP version (5.4.0) is not sufficient enough for the package "package/second", which requires >=5.6
     */
    public function testCombinedPhpVersionConstraint()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = [
            'name' => 'test',
            'package' => [
                'php' => '>=5.3.3',
            ],
            'combined' => [
                [
                    'name' => 'package/first',
                    'package' => [
                        'php' => '>=5.4.0',
                    ],
                ],
                [
                    'name' => 'package/second',
                    'package' => [
                        'php' => '>=5.6',
                    ],
                ],
            ],
        ];

        $this->command->setConfiguration($config);
        $this->command->setPhpVersion('5.4.0');

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ]);
    }

    public function testDoesNotRunScriptsIfSkipped()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = ['name' => 'test'];
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

        $tasks = [
            new \stdClass(),
            new \stdClass(),
        ];
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

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
            '--skip-scripts' => null,
        ]);
    }
}
