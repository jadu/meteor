<?php

namespace Meteor\Patch\Cli\Command;

use InvalidArgumentException;
use Meteor\IO\IOInterface;
use Meteor\Logger\LoggerInterface;
use Meteor\Package\PackageConstants;
use Meteor\Patch\Backup\BackupFinder;
use Meteor\Patch\Event\PatchEvents;
use Meteor\Patch\Lock\Locker;
use Meteor\Patch\Strategy\PatchStrategyInterface;
use Meteor\Patch\Task\TaskBusInterface;
use Meteor\Patch\Version\VersionComparer;
use Meteor\Platform\PlatformInterface;
use Meteor\Scripts\ScriptRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RollbackCommand extends AbstractPatchCommand
{
    /**
     * @var VersionComparer
     */
    private $versionComparer;

    /**
     * @var BackupFinder
     */
    private $backupFinder;

    /**
     * @var TaskBusInterface
     */
    private $taskBus;

    /**
     * @var PatchStrategyInterface
     */
    private $strategy;

    /**
     * @var Locker
     */
    private $locker;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ScriptRunner
     */
    private $scriptRunner;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string $name
     * @param array $config
     * @param IOInterface $io
     * @param PlatformInterface $platform
     * @param VersionComparer $versionComparer
     * @param BackupFinder $backupFinder
     * @param TaskBusInterface $taskBus
     * @param PatchStrategyInterface $strategy
     * @param Locker $locker
     * @param EventDispatcherInterface $eventDispatcher
     * @param ScriptRunner $scriptRunner
     * @param LoggerInterface $logger
     */
    public function __construct(
        $name,
        array $config,
        IOInterface $io,
        PlatformInterface $platform,
        VersionComparer $versionComparer,
        BackupFinder $backupFinder,
        TaskBusInterface $taskBus,
        PatchStrategyInterface $strategy,
        Locker $locker,
        EventDispatcherInterface $eventDispatcher,
        ScriptRunner $scriptRunner,
        LoggerInterface $logger
    ) {
        $this->versionComparer = $versionComparer;
        $this->backupFinder = $backupFinder;
        $this->taskBus = $taskBus;
        $this->strategy = $strategy;
        $this->locker = $locker;
        $this->eventDispatcher = $eventDispatcher;
        $this->scriptRunner = $scriptRunner;
        $this->logger = $logger;

        parent::__construct($name, $config, $io, $platform);
    }

    protected function configure()
    {
        $this->setName('patch:rollback');
        $this->setDescription('Rolls back a previous patch');

        $this->addOption('skip-lock', null, InputOption::VALUE_NONE, 'Skip any existing lock files to force a rollback');
        $this->strategy->configureRollbackCommand($this->getDefinition());

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workingDir = $this->getWorkingDir();
        $installDir = $this->getInstallDir();

        if ($workingDir === $installDir) {
            throw new InvalidArgumentException('The working directory cannot be the same as the install directory');
        }

        $config = $this->getConfiguration();

        $this->platform->setInstallDir($installDir);
        $this->scriptRunner->setWorkingDir($installDir);

        $packageValid = true;
        $versions = $this->versionComparer->comparePackage($workingDir.'/'.PackageConstants::PATCH_DIR, $installDir, $config);
        foreach ($versions as $version) {
            if ($version->isLessThan()) {
                // The version in the package is less than the version in the install so the package is not valid
                $packageValid = false;
                break;
            }
        }

        if (!$packageValid) {
            $this->io->error('The package versions must be greater than or equal to the currently installed versions.');

            return 1;
        }

        $backups = $this->backupFinder->find($installDir, $config);
        if (empty($backups)) {
            $this->io->error('Unable to find a valid backup for this package.');

            return 1;
        }

        $backupChoices = array();
        $backupRows = array();
        foreach ($backups as $index => $backup) {
            $backupChoices[] = $index;

            $backupVersions = array();
            foreach ($backup->getVersions() as $version) {
                $backupVersions[] = sprintf('<comment>%s</>: %s', $version->getPackageName(), $version->getNewVersion());
            }

            $backupRows[] = array(
                $index,
                $backup->getDate()->format('c'),
                implode($backupVersions, ', '),
            );
        }

        $this->io->table(array(
            'Choice',
            'Date',
            'Versions',
        ), $backupRows);

        // NB: The first backup will be the most recent
        $backupChoice = (int) $this->io->ask('Please select a backup to rollback to:', 0);
        if (!isset($backups[$backupChoice])) {
            $this->io->error('Please select an available backup.');

            return 1;
        }

        $backup = $backups[$backupChoice];

        $this->logger->enable($this->getLogPath($workingDir));

        $this->io->title(sprintf('Rolling back the <info>%s</> patch to version <info>%s</>', $config['name'], $backup->getDate()->format('c')));

        if (!$this->io->getOption('skip-lock')) {
            $this->locker->lock($installDir);
        }

        $this->eventDispatcher->dispatch(PatchEvents::PRE_ROLLBACK, new Event());

        if ($backupChoice > 0 && !empty($backups)) {
            $intermediateBackups = array_slice($backups, 0, $backupChoice);

            $intermediateBackupDirs = array_map(function ($backup) {
                return $backup->getPath();
            }, $intermediateBackups);

            $intermediateBackupDates = array_map(function ($backup) {
                return $backup->getDate()->format('c');
            }, $intermediateBackups);

            $this->io->note('The latest backup was not chosen so intermediate backups will be removed.');
            $this->io->text('Intermediate backups to be removed:');
            $this->io->listing($intermediateBackupDates);
        } else {
            $intermediateBackupDirs = array();
        }

        $tasks = $this->strategy->rollback($backup->getPath(), $workingDir, $installDir, $intermediateBackupDirs, $this->io->getOptions());
        foreach ($tasks as $task) {
            $result = $this->taskBus->run($task, $config);
            if ($result === false) {
                return 1;
            }
        }

        $this->eventDispatcher->dispatch(PatchEvents::POST_ROLLBACK, new Event());

        if (!$this->io->getOption('skip-lock')) {
            $this->locker->unlock($installDir);
        }

        $this->io->success('Rollback complete');
    }
}
