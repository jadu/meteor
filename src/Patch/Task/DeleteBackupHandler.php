<?php

namespace Meteor\Patch\Task;

use Meteor\Filesystem\Filesystem;
use Meteor\IO\IOInterface;

class DeleteBackupHandler
{
    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param IOInterface $io
     * @param Filesystem $filesystem
     */
    public function __construct(IOInterface $io, Filesystem $filesystem)
    {
        $this->io = $io;
        $this->filesystem = $filesystem;
    }

    /**
     * @param DeleteBackup $task
     */
    public function handle(DeleteBackup $task)
    {
        $this->io->text(sprintf('Removing the backup <info>%s</>', $task->backupDir));
        $this->filesystem->remove($task->backupDir);
    }
}
