<?php

namespace Meteor\Patch\Cli\Command;

use Meteor\Cli\Command\CommandTestCase;
use Meteor\IO\NullIO;
use Meteor\Patch\Backup\Backup;
use Meteor\Patch\Event\PatchEvents;
use Meteor\Permissions\PermissionSetter;
use Mockery;
use org\bovigo\vfs\vfsStream;

class RollbackCommandTest extends CommandTestCase
{
    private $versionComparer;
    private $backupFinder;
    private $taskBus;
    private $strategy;
    private $platform;
    private $locker;
    private $eventDispatcher;
    private $scriptRunner;
    private $permissionSetter;
    private $logger;

    public function createCommand()
    {
        vfsStream::setup('root', null, [
            'patch' => [],
            'install' => [],
        ]);

        $this->versionComparer = Mockery::mock('Meteor\Patch\Version\VersionComparer');
        $this->backupFinder = Mockery::mock('Meteor\Patch\Backup\BackupFinder');
        $this->taskBus = Mockery::mock('Meteor\Patch\Task\TaskBusInterface');
        $this->strategy = Mockery::mock('Meteor\Patch\Strategy\PatchStrategyInterface');
        $this->permissionSetter = Mockery::mock(PermissionSetter::class,['setPostScriptsPermissions' => null]);

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

        $this->strategy->shouldReceive('configureRollbackCommand')
            ->once();

        return new RollbackCommand(
            null,
            [],
            new NullIO(),
            $this->platform,
            $this->versionComparer,
            $this->backupFinder,
            $this->taskBus,
            $this->strategy,
            $this->locker,
            $this->eventDispatcher,
            $this->scriptRunner,
            $this->logger,
            $this->permissionSetter
        );
    }

    public function testRollsBackPatch()
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

        $this->versionComparer->shouldReceive('comparePackage')
            ->with($workingDir . '/to_patch', $installDir, $config)
            ->andReturn([])
            ->once();

        $this->permissionSetter->shouldReceive('setPostScriptsPermissions')
            ->with($installDir)
            ->once();

        $backups = [
            new Backup(vfsStream::url('root/install/backups/20160701102030'), []),
            new Backup(vfsStream::url('root/install/backups/20160702102030'), []),
            new Backup(vfsStream::url('root/install/backups/20160703102030'), []),
        ];

        $this->backupFinder->shouldReceive('find')
            ->with($installDir . '/backups', $installDir, $config)
            ->andReturn($backups)
            ->once();

        $backupDir = $backups[0]->getPath();

        $this->logger->shouldReceive('enable')
            ->once();

        $this->locker->shouldReceive('lock')
            ->with($installDir)
            ->once();

        $this->eventDispatcher->shouldReceive('dispatch')
            ->with(PatchEvents::PRE_ROLLBACK, Mockery::any())
            ->once();

        $tasks = [
            new \stdClass(),
            new \stdClass(),
        ];
        $this->strategy->shouldReceive('rollback')
            ->with($backupDir, $workingDir, $installDir, [], Mockery::any())
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
            ->with(PatchEvents::POST_ROLLBACK, Mockery::any())
            ->once();

        $this->locker->shouldReceive('unlock')
            ->with($installDir)
            ->once();

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

        $this->versionComparer->shouldReceive('comparePackage')
            ->with($workingDir . '/to_patch', $installDir, $config)
            ->andReturn([]);

        $backups = [
            new Backup(vfsStream::url('root/install/backups/20160701102030'), []),
            new Backup(vfsStream::url('root/install/backups/20160702102030'), []),
            new Backup(vfsStream::url('root/install/backups/20160703102030'), []),
        ];

        $this->backupFinder->shouldReceive('find')
            ->with($installDir . '/backups', $installDir, $config)
            ->andReturn($backups);

        $backupDir = $backups[0]->getPath();

        $this->logger->shouldReceive('enable');

        $this->locker->shouldReceive('lock')
            ->with($installDir);

        $this->eventDispatcher->shouldReceive('dispatch')
            ->with(PatchEvents::PRE_ROLLBACK, Mockery::any())
            ->never();

        $tasks = [
            new \stdClass(),
            new \stdClass(),
        ];
        $this->strategy->shouldReceive('rollback')
            ->with($backupDir, $workingDir, $installDir, [], Mockery::any())
            ->andReturn($tasks);

        $this->taskBus->shouldReceive('run')
            ->with($tasks[0], $config)
            ->andReturn(true);

        $this->taskBus->shouldReceive('run')
            ->with($tasks[1], $config)
            ->andReturn(true);

        $this->eventDispatcher->shouldReceive('dispatch')
            ->with(PatchEvents::POST_ROLLBACK, Mockery::any())
            ->never();

        $this->locker->shouldReceive('unlock')
            ->with($installDir);

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
            '--skip-scripts' => null,
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
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

        $this->versionComparer->shouldReceive('comparePackage')
            ->with($workingDir . '/to_patch', $installDir, $config)
            ->andReturn([])
            ->once();

        $backups = [
            new Backup(vfsStream::url('root/install/backups/20160701102030'), []),
            new Backup(vfsStream::url('root/install/backups/20160702102030'), []),
            new Backup(vfsStream::url('root/install/backups/20160703102030'), []),
        ];

        $this->backupFinder->shouldReceive('find')
            ->with($installDir . '/backups', $installDir, $config)
            ->andReturn($backups)
            ->once();

        $backupDir = $backups[0]->getPath();

        $this->logger->shouldReceive('enable')
            ->once();

        $this->locker->shouldReceive('lock')
            ->never();

        $tasks = [new \stdClass()];
        $this->strategy->shouldReceive('rollback')
            ->with($backupDir, $workingDir, $installDir, [], Mockery::any())
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

        $this->versionComparer->shouldReceive('comparePackage')
            ->with($workingDir . '/to_patch', $installDir, $config)
            ->andReturn([])
            ->once();

        $backups = [
            new Backup(vfsStream::url('root/install/backups/20160701102030'), []),
            new Backup(vfsStream::url('root/install/backups/20160702102030'), []),
            new Backup(vfsStream::url('root/install/backups/20160703102030'), []),
        ];

        $this->backupFinder->shouldReceive('find')
            ->with($installDir . '/backups', $installDir, $config)
            ->andReturn($backups)
            ->once();

        $backupDir = $backups[0]->getPath();

        $this->logger->shouldReceive('enable')
            ->once();

        $this->locker->shouldReceive('lock')
            ->with($installDir)
            ->once();

        $tasks = [new \stdClass()];
        $this->strategy->shouldReceive('rollback')
            ->with($backupDir, $workingDir, $installDir, [], Mockery::any())
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
}
