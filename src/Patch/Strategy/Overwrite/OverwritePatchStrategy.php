<?php

namespace Meteor\Patch\Strategy\Overwrite;

use Meteor\Migrations\MigrationsConstants;
use Meteor\Package\PackageConstants;
use Meteor\Patch\Strategy\PatchStrategyInterface;
use Meteor\Patch\Task\BackupFiles;
use Meteor\Patch\Task\CheckDatabaseConnection;
use Meteor\Patch\Task\CheckDiskSpace;
use Meteor\Patch\Task\CheckModuleCmsDependency;
use Meteor\Patch\Task\CheckVersion;
use Meteor\Patch\Task\CheckWritePermission;
use Meteor\Patch\Task\CopyFiles;
use Meteor\Patch\Task\DeleteBackup;
use Meteor\Patch\Task\DisplayVersionInfo;
use Meteor\Patch\Task\MigrateDown;
use Meteor\Patch\Task\MigrateUp;
use Meteor\Patch\Task\UpdateMigrationVersionFiles;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class OverwritePatchStrategy implements PatchStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply($patchDir, $installDir, array $options)
    {
        $tasks = array();
        $patchFilesDir = $patchDir.'/'.PackageConstants::PATCH_DIR;
        $backupDir = $installDir.'/backups/'.date('YmdHis');

        $tasks[] = new CheckWritePermission($installDir);
        $tasks[] = new DisplayVersionInfo($patchFilesDir, $installDir);

        if (!$options['skip-version-check']) {
            $tasks[] = new CheckModuleCmsDependency($patchFilesDir, $installDir);
            $tasks[] = new CheckVersion($patchFilesDir, $installDir, CheckVersion::GREATER_THAN_OR_EQUAL);
        }

        if (!$options['skip-db-migrations'] || !$options['skip-file-migrations']) {
            $tasks[] = new CheckDatabaseConnection($installDir);
        }

        $tasks[] = new CheckDiskSpace($installDir);

        if (!$options['skip-backup']) {
            $tasks[] = new BackupFiles($backupDir, $patchDir, $installDir);
            $tasks[] = new UpdateMigrationVersionFiles($backupDir, $patchDir, $installDir);
        }

        $tasks[] = new CopyFiles($patchFilesDir, $installDir);

        if (!$options['skip-db-migrations']) {
            $tasks[] = new MigrateUp($patchDir, $installDir, MigrationsConstants::TYPE_DATABASE);
        }

        if (!$options['skip-file-migrations']) {
            $tasks[] = new MigrateUp($patchDir, $installDir, MigrationsConstants::TYPE_FILE);
        }

        return $tasks;
    }

    /**
     * {@inheritdoc}
     */
    public function configureApplyCommand(InputDefinition $definition)
    {
        $definition->addOption(new InputOption('skip-backup', null, InputOption::VALUE_NONE, 'Do not create a backup before applying the patch.'));

        $this->configureCommand($definition);
    }

    /**
     * {@inheritdoc}
     */
    public function rollback($backupDir, $patchDir, $installDir, array $intermediateBackupDirs, array $options)
    {
        $tasks = array();
        $backupFilesDir = $backupDir.'/'.PackageConstants::PATCH_DIR;

        $tasks[] = new CheckWritePermission($installDir);
        $tasks[] = new DisplayVersionInfo($backupFilesDir, $installDir);

        if (!$options['skip-version-check']) {
            $tasks[] = new CheckVersion($backupFilesDir, $installDir, CheckVersion::LESS_THAN_OR_EQUAL);
        }

        if (!$options['skip-db-migrations'] || !$options['skip-file-migrations']) {
            $tasks[] = new CheckDatabaseConnection($installDir);
        }

        $tasks[] = new CopyFiles($backupFilesDir, $installDir);

        if (!$options['skip-db-migrations']) {
            $tasks[] = new MigrateDown($backupDir, $patchDir, $installDir, MigrationsConstants::TYPE_DATABASE);
        }

        if (!$options['skip-file-migrations']) {
            $tasks[] = new MigrateDown($backupDir, $patchDir, $installDir, MigrationsConstants::TYPE_FILE);
        }

        foreach ($intermediateBackupDirs as $intermediateBackupDir) {
            $tasks[] = new DeleteBackup($intermediateBackupDir);
        }

        $tasks[] = new DeleteBackup($backupDir);

        return $tasks;
    }

    /**
     * {@inheritdoc}
     */
    public function configureRollbackCommand(InputDefinition $definition)
    {
        $this->configureCommand($definition);
    }

    /**
     * {@inheritdoc}
     */
    private function configureCommand(InputDefinition $definition)
    {
        $definition->addOption(new InputOption('skip-db-migrations', null, InputOption::VALUE_NONE, 'Skip database migrations.'));
        $definition->addOption(new InputOption('skip-file-migrations', null, InputOption::VALUE_NONE, 'Skip file migrations.'));
        $definition->addOption(new InputOption('skip-version-check', null, InputOption::VALUE_NONE, 'Skip the version check.'));
    }
}
