<?php

namespace Meteor\Patch\Task;

use Meteor\Filesystem\Filesystem;
use Meteor\IO\IOInterface;
use Meteor\Patch\Backup\BackupFinder;
use Meteor\Patch\Backup\BackupHandlerTrait;

class CheckDiskSpaceHandler
{
    use BackupHandlerTrait;

    /**
     * Assuming required space is 300MB for backup and new files. Not checking the real package
     * size to avoid performance issues when checking the size of thousands of files.
     */
    const REQUIRED_BYTES = 314572800;

    /**
     * The required free space as a percentage.
     */
    const REQUIRED_FREE_SPACE_PERCENT = 10;

    /**
     * The maximum number of backups to keep when running low on disk space.
     */
    const MAX_BACKUPS = 2;

    /**
     * @var BackupFinder
     */
    private $backupFinder;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param BackupFinder $backupFinder
     * @param Filesystem $filesystem
     * @param IOInterface $io
     */
    public function __construct(BackupFinder $backupFinder, Filesystem $filesystem, IOInterface $io)
    {
        $this->backupFinder = $backupFinder;
        $this->filesystem = $filesystem;
        $this->io = $io;
    }

    /**
     * @param CheckDiskSpace $task
     * @param array          $config
     *
     * @return bool
     */
    public function handle(CheckDiskSpace $task, array $config)
    {
        if ($this->hasFreeSpace($task->installDir)) {
            // Plenty of space available
            return true;
        }

        $this->io->warning('Patching will reduce free disk space to less than ' . self::REQUIRED_FREE_SPACE_PERCENT . '%');

        // Try removing old backups
        $this->removeOldBackups($task->installDir, $config);

        // Check disk space again
        if ($this->hasFreeSpace($task->installDir)) {
            return true;
        }

        $confirmation = $this->io->askConfirmation('Would you like to continue anyway?', false);
        if (!$confirmation) {
            return false;
        }

        return true;
    }

    /**
     * @param string $installDir
     *
     * @return bool
     */
    private function hasFreeSpace($installDir)
    {
        $totalSpace = disk_total_space($installDir);
        $freeSpace = disk_free_space($installDir) - self::REQUIRED_BYTES;

        $freeSpacePercent = ($freeSpace / $totalSpace) * 100;

        return $freeSpacePercent > self::REQUIRED_FREE_SPACE_PERCENT;
    }

    /**
     * @param string $installDir
     * @param array $config
     */
    private function removeOldBackups($installDir, array $config)
    {
        $backups = $this->backupFinder->find($installDir . '/backups', $installDir, $config);
        if (count($backups) <= self::MAX_BACKUPS) {
            // No backups to remove
            return;
        }

        $this->removeBackups($backups, self::MAX_BACKUPS);
    }
}
