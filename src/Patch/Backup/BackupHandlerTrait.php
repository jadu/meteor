<?php

namespace Meteor\Patch\Backup;

trait BackupHandlerTrait
{
    public function removeBackups($backups, $count)
    {
        // Do not remove the latest backups that can be kept
        $backups = array_slice($backups, $count);

        $this->io->text(sprintf('It is recommended to keep a maximum of %d backups. To free up some disk space the following %d backups should be removed: ', $count, count($backups)));

        // Get just the backup directory names
        $backupDirs = array_map(function ($backup) {
            return $backup->getPath();
        }, $backups);

        $this->io->listing($backupDirs);

        $confirmation = $this->io->askConfirmation('Would you like to remove these backups?', true);
        if (!$confirmation) {
            return;
        }

        foreach ($backups as $backup) {
            $this->io->text(sprintf('Removing backup <info>%s</info>', $backup->getDate()->format('c')));
            $this->filesystem->remove($backup->getPath());
        }
    }
}
