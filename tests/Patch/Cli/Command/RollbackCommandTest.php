<?php

namespace Meteor\Patch\Cli\Command;

use Meteor\Cli\Command\CommandTestCase;
use Meteor\IO\NullIO;
use Meteor\Patch\Backup\Backup;
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
    private $logger;

    public function createCommand()
    {
        vfsStream::setup('root', null, array(
            'patch' => array(),
            'install' => array(),
        ));

        $this->versionComparer = Mockery::mock('Meteor\Patch\Version\VersionComparer');
        $this->backupFinder = Mockery::mock('Meteor\Patch\Backup\BackupFinder');
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

        $this->strategy->shouldReceive('configureRollbackCommand')
            ->once();

        return new RollbackCommand(
            null,
            array(),
            new NullIO(),
            $this->platform,
            $this->versionComparer,
            $this->backupFinder,
            $this->taskBus,
            $this->strategy,
            $this->locker,
            $this->eventDispatcher,
            $this->scriptRunner,
            $this->logger
        );
    }

    public function testRollsBackPatch()
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

        $this->versionComparer->shouldReceive('comparePackage')
            ->with($workingDir.'/to_patch', $installDir, $config)
            ->andReturn(array())
            ->once();

        $backups = array(
            new Backup(vfsStream::url('root/install/backups/20160701102030'), array()),
            new Backup(vfsStream::url('root/install/backups/20160702102030'), array()),
            new Backup(vfsStream::url('root/install/backups/20160703102030'), array()),
        );

        $this->backupFinder->shouldReceive('find')
            ->with($installDir, $config)
            ->andReturn($backups)
            ->once();

        $backupDir = $backups[0]->getPath();

        $this->logger->shouldReceive('enable')
            ->once();

        $this->locker->shouldReceive('lock')
            ->with($installDir)
            ->once();

        $tasks = array(
            new \stdClass(),
            new \stdClass(),
        );
        $this->strategy->shouldReceive('rollback')
            ->with($backupDir, $workingDir, $installDir, array(), Mockery::any())
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

        $this->versionComparer->shouldReceive('comparePackage')
            ->with($workingDir.'/to_patch', $installDir, $config)
            ->andReturn(array())
            ->once();

        $backups = array(
            new Backup(vfsStream::url('root/install/backups/20160701102030'), array()),
            new Backup(vfsStream::url('root/install/backups/20160702102030'), array()),
            new Backup(vfsStream::url('root/install/backups/20160703102030'), array()),
        );

        $this->backupFinder->shouldReceive('find')
            ->with($installDir, $config)
            ->andReturn($backups)
            ->once();

        $backupDir = $backups[0]->getPath();

        $this->logger->shouldReceive('enable')
            ->once();

        $this->locker->shouldReceive('lock')
            ->never();

        $tasks = array(new \stdClass());
        $this->strategy->shouldReceive('rollback')
            ->with($backupDir, $workingDir, $installDir, array(), Mockery::any())
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

        $this->versionComparer->shouldReceive('comparePackage')
            ->with($workingDir.'/to_patch', $installDir, $config)
            ->andReturn(array())
            ->once();

        $backups = array(
            new Backup(vfsStream::url('root/install/backups/20160701102030'), array()),
            new Backup(vfsStream::url('root/install/backups/20160702102030'), array()),
            new Backup(vfsStream::url('root/install/backups/20160703102030'), array()),
        );

        $this->backupFinder->shouldReceive('find')
            ->with($installDir, $config)
            ->andReturn($backups)
            ->once();

        $backupDir = $backups[0]->getPath();

        $this->logger->shouldReceive('enable')
            ->once();

        $this->locker->shouldReceive('lock')
            ->with($installDir)
            ->once();

        $tasks = array(new \stdClass());
        $this->strategy->shouldReceive('rollback')
            ->with($backupDir, $workingDir, $installDir, array(), Mockery::any())
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
}
