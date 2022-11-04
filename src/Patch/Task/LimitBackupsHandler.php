<?php

namespace Meteor\Patch\Task;

use Meteor\Filesystem\Filesystem;
use Meteor\IO\IOInterface;
use Meteor\Patch\Backup\BackupFinder;
use Meteor\Patch\Backup\BackupHandlerTrait;

class LimitBackupsHandler
{
    use BackupHandlerTrait;

    /**
     * @var BackupFinder
     */
    private $backupFinder;

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
     * @param LimitBackups $task
     * @param array $config
     *
     * @return bool
     */
    public function handle(LimitBackups $task, array $config)
    {
        $this->filesystem->ensureDirectoryExists($task->backupsDir);

        $backups = $this->backupFinder->find($task->backupsDir, $task->installDir, $config);

        if ((int) $task->backups === 0 || count($backups) <= $task->backups) {
            // No backups to remove
            return true;
        }

        $this->removeBackups($backups, $task->backups);

        return true;
    }
}
