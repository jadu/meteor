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
     * Free space must be at least patch size multiplied by this number. Should
     * ensure we set this large enough to make backup copies of everything in
     * the patch.
     */
    public const PATCH_SIZE_MULTIPLIER = 2.5;

    /**
     * The maximum number of backups to keep when running low on disk space.
     */
    public const MAX_BACKUPS = 2;

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
     * @param array $config
     *
     * @return bool
     */
    public function handle(CheckDiskSpace $task, array $config)
    {
        $spaceRequired = $this->calculateRequiredDiskSpace($task->patchFilesDir);

        if ($this->hasFreeSpace($task->installDir, $spaceRequired)) {
            // Plenty of space available
            return true;
        }

        $this->io->warning(sprintf(
            'There is not enough free disk space to apply this patch. Space required: %s, Space available: %s',
            $this->io->formatFileSize($spaceRequired),
            $this->io->formatFileSize(disk_free_space($task->installDir))
        ));

        // Try removing old backups
        $this->removeOldBackups($task->backupsDir, $task->installDir, $config);

        // Check disk space again
        if ($this->hasFreeSpace($task->installDir, $spaceRequired)) {
            return true;
        }

        $confirmation = $this->io->askConfirmation('Would you like to continue anyway?', false);
        if (!$confirmation) {
            return false;
        }

        return true;
    }

    private function calculateRequiredDiskSpace($patchDirectory)
    {
        $patchSize = $this->filesystem->getDirectorySize($patchDirectory);

        return $patchSize * static::PATCH_SIZE_MULTIPLIER;
    }

    /**
     * @param string $installDir
     * @param int $spaceRequired
     *
     * @return bool
     */
    private function hasFreeSpace($installDir, $spaceRequired)
    {
        $freeSpace = disk_free_space($installDir);

        $this->io->debug(sprintf(
            'Available disk space: %s',
            $this->io->formatFileSize($freeSpace)
        ));

        $this->io->debug(sprintf(
            'Disk space required: %s',
            $this->io->formatFileSize($spaceRequired)
        ));

        $resultingSpace = $freeSpace - $spaceRequired;

        return $resultingSpace > 0;
    }

    /**
     * @param string $backupsDir
     * @param string $installDir
     * @param array $config
     */
    private function removeOldBackups($backupsDir, $installDir, array $config)
    {
        $backups = $this->backupFinder->find($backupsDir, $installDir, $config);
        if (count($backups) <= self::MAX_BACKUPS) {
            // No backups to remove
            return;
        }

        $this->removeBackups($backups, self::MAX_BACKUPS);
    }
}
