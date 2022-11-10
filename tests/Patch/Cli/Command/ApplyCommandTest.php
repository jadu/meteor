<?php

namespace Meteor\Patch\Cli\Command;

use InvalidArgumentException;
use Meteor\Cli\Command\CommandTestCase;
use Meteor\IO\NullIO;
use Meteor\Patch\Event\PatchEvents;
use Meteor\Patch\Exception\PhpVersionException;
use Meteor\Patch\Manifest\ManifestChecker;
use Meteor\Permissions\PermissionSetter;
use Mockery;
use org\bovigo\vfs\vfsStream;
use stdClass;

class ApplyCommandTest extends CommandTestCase
{
    private $taskBus;
    private $strategy;
    private $platform;
    private $locker;
    private $manifestChecker;
    private $eventDispatcher;
    private $scriptRunner;
    private $logger;
    protected $permissionSetter;

    public function createCommand()
    {
        vfsStream::setup('root', null, [
            'patch' => [],
            'install' => [],
            'logs' => [],
        ]);

        $this->taskBus = Mockery::mock('Meteor\Patch\Task\TaskBusInterface');
        $this->strategy = Mockery::mock('Meteor\Patch\Strategy\PatchStrategyInterface');
        $this->platform = Mockery::mock('Meteor\Platform\PlatformInterface', [
            'setInstallDir' => null,
        ]);
        $this->locker = Mockery::mock('Meteor\Patch\Lock\Locker');
        $this->manifestChecker = Mockery::mock(ManifestChecker::class);
        $this->eventDispatcher = Mockery::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface', [
            'dispatch' => new stdClass(),
        ]);
        $this->scriptRunner = Mockery::mock('Meteor\Scripts\ScriptRunner', [
            'setWorkingDir' => null,
        ]);
        $this->logger = Mockery::mock('Meteor\Logger\LoggerInterface');
        $this->permissionSetter = Mockery::mock(PermissionSetter::class, ['setPostScriptsPermissions' => null]);

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
            $this->manifestChecker,
            $this->eventDispatcher,
            $this->scriptRunner,
            $this->logger,
            $this->permissionSetter
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

        $this->manifestChecker->shouldReceive('check')
            ->with($workingDir)
            ->once();

        $this->permissionSetter->shouldReceive('setPostScriptsPermissions')
            ->with($installDir)
            ->once();

        $this->locker->shouldReceive('lock')
            ->with($installDir)
            ->once();

        $this->eventDispatcher->shouldReceive('dispatch')
            ->with(PatchEvents::PRE_APPLY, Mockery::any())
            ->once();

        $tasks = [
            new stdClass(),
            new stdClass(),
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
            ->with(Mockery::any(), PatchEvents::POST_APPLY)
            ->andReturn(new stdClass())
            ->once();

        $this->locker->shouldReceive('unlock')
            ->with($installDir)
            ->once();

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ]);
    }

    public function testThrowsExceptionWhenWorkingDirIsTheSameAsTheInstallDir()
    {
        $this->expectException(InvalidArgumentException::class);

        $workingDir = vfsStream::url('root/install');
        $installDir = vfsStream::url('root/install');

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ]);
    }

    public function testDoesCheckManifestWhenSkipVerifyOptionSpecified()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = ['name' => 'test'];
        $this->command->setConfiguration($config);

        $this->logger->shouldReceive('enable')
            ->once();

        $this->manifestChecker->shouldReceive('check')
            ->never();

        $this->locker->shouldReceive('lock')
            ->with($installDir)
            ->once();

        $tasks = [new stdClass()];
        $this->strategy->shouldReceive('apply')
            ->with($workingDir, $installDir, Mockery::any())
            ->andReturn($tasks)
            ->once();

        $this->taskBus->shouldReceive('run')
            ->with($tasks[0], $config)
            ->andReturn(true)
            ->once();

        $this->permissionSetter->shouldReceive('setPostScriptsPermissions')
            ->with($installDir)
            ->once();

        $this->locker->shouldReceive('unlock')
            ->with($installDir)
            ->once();

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
            '--skip-verify' => null,
        ]);
    }

    public function testStopsApplyingIfManifestCheckReturnsFalse()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = ['name' => 'test'];
        $this->command->setConfiguration($config);

        $this->logger->shouldReceive('enable')
            ->once();

        $this->manifestChecker->shouldReceive('check')
            ->with($workingDir)
            ->andReturn(false)
            ->once();

        $this->taskBus->shouldReceive('run')
            ->never();

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ]);

        static::assertSame(1, $this->tester->getStatusCode());
    }

    public function testDoesNotLockWhenSkipLockOptionSpecified()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = ['name' => 'test'];
        $this->command->setConfiguration($config);

        $this->logger->shouldReceive('enable')
            ->once();

        $this->manifestChecker->shouldReceive('check')
            ->with($workingDir)
            ->once();

        $this->locker->shouldReceive('lock')
            ->never();

        $tasks = [new stdClass()];
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

        $this->manifestChecker->shouldReceive('check')
            ->with($workingDir)
            ->once();

        $this->locker->shouldReceive('lock')
            ->with($installDir)
            ->once();

        $tasks = [new stdClass()];
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

    public function testPhpVersionConstraint()
    {
        $this->expectException(PhpVersionException::class);
        $this->expectExceptionMessage('Your PHP version (5.6.0) is not sufficient enough for the package "test", which requires >=7');

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
                'php' => '>=5.3.2',
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

        $this->manifestChecker->shouldReceive('check')
            ->with($workingDir)
            ->once();

        $this->locker->shouldReceive('lock')
            ->with($installDir)
            ->once();

        $this->eventDispatcher->shouldReceive('dispatch')
            ->with(PatchEvents::PRE_APPLY, Mockery::any())
            ->once();

        $tasks = [
            new stdClass(),
            new stdClass(),
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
                'php' => '>=5.3.2',
            ],
        ];

        $this->command->setConfiguration($config);
        $this->command->setPhpVersion('5.6.0-1ubuntu3.25');

        static::assertEquals('5.6.0', $this->command->getPhpVersion());
    }

    public function testCombinedPhpVersionConstraint()
    {
        $this->expectException(PhpVersionException::class);
        $this->expectExceptionMessage('Your PHP version (5.4.0) is not sufficient enough for the package "package/second", which requires >=5.6');

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

        $this->manifestChecker->shouldReceive('check')
            ->with($workingDir);

        $this->locker->shouldReceive('lock')
            ->with($installDir);

        $this->eventDispatcher->shouldReceive('dispatch')
            ->with(PatchEvents::PRE_APPLY, Mockery::any())
            ->never();

        $tasks = [
            new stdClass(),
            new stdClass(),
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

    public function testChangesLogDirectoryIfPassedParameter()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');
        $logDir = vfsStream::url('root/logs');
        $filename = 'meteor-' . date('YmdHis') . '.log';

        $config = ['name' => 'test'];
        $this->command->setConfiguration($config);

        $this->logger->shouldReceive('enable')
            ->with($logDir . '/' . $filename)
            ->once();

        $this->manifestChecker->shouldReceive('check')
            ->with($workingDir)
            ->once();

        $this->locker->shouldReceive('lock')
            ->never();

        $tasks = [new stdClass()];
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
            '--log-dir' => $logDir,
        ]);
    }

    public function testChangesLogDirectoryThrowsErrorIfDoesntExist()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The log directory `/testlog` does not exist.');

        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = ['name' => 'test'];
        $this->command->setConfiguration($config);

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
            '--skip-lock' => null,
            '--log-dir' => '/testlog',
        ]);
    }

    public function testDoesNotSetPostApplyPermissionIfSkipVerifyOptionSpecified()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = ['name' => 'test'];
        $this->command->setConfiguration($config);

        $this->logger->shouldReceive('enable')
            ->once();

        $this->manifestChecker->shouldReceive('check')
            ->with($workingDir)
            ->once();

        $this->permissionSetter->shouldReceive('setPostScriptsPermissions')
            ->with($installDir)
            ->never();

        $this->locker->shouldReceive('lock')
            ->with($installDir)
            ->once();

        $tasks = [new stdClass()];
        $this->strategy->shouldReceive('apply')
            ->with($workingDir, $installDir, Mockery::any())
            ->andReturn($tasks)
            ->once();

        $this->taskBus->shouldReceive('run')
            ->with($tasks[0], $config)
            ->andReturn(true)
            ->once();

        $this->locker->shouldReceive('unlock')
            ->with($installDir)
            ->once();

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
            '--skip-post-scripts-permissions' => null,
        ]);
    }
}
