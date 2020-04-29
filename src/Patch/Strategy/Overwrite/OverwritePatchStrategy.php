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
use Meteor\Patch\Task\LimitBackups;
use Meteor\Patch\Task\MigrateDown;
use Meteor\Patch\Task\MigrateUp;
use Meteor\Patch\Task\SetPermissions;
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
        $tasks = [];
        $patchFilesDir = $patchDir . '/' . PackageConstants::PATCH_DIR;
        $backupDir = $installDir . '/backups/' . date('YmdHis');

        $tasks[] = new CheckWritePermission($installDir);
        $tasks[] = new DisplayVersionInfo($patchFilesDir, $installDir);

        if (!$options['skip-version-check']) {
            $tasks[] = new CheckModuleCmsDependency($patchFilesDir, $installDir);
            $tasks[] = new CheckVersion($patchFilesDir, $installDir, CheckVersion::GREATER_THAN_OR_EQUAL);
        }

        if (!$options['skip-db-migrations'] || !$options['skip-file-migrations']) {
            $tasks[] = new CheckDatabaseConnection($installDir);
        }

        if (!empty($options['limit-backups']) && (int) $options['limit-backups'] !== 0) {
            $tasks[] = new LimitBackups($installDir, $options['limit-backups']);
        }

        $tasks[] = new CheckDiskSpace($installDir);

        if (!$options['skip-backup']) {
            $tasks[] = new BackupFiles($backupDir, $patchDir, $installDir);
            $tasks[] = new UpdateMigrationVersionFiles($backupDir, $patchDir, $installDir);
        }

        $tasks[] = new CopyFiles($patchFilesDir, $installDir);

        if (!$options['skip-db-migrations']) {
            $tasks[] = new MigrateUp($patchDir, $installDir, MigrationsConstants::TYPE_DATABASE, (bool) $options['ignore-unavailable-migrations']);
        }

        if (!$options['skip-file-migrations']) {
            $tasks[] = new MigrateUp($patchDir, $installDir, MigrationsConstants::TYPE_FILE, (bool) $options['ignore-unavailable-migrations']);
        }

        $tasks[] = new SetPermissions($patchFilesDir, $installDir);

        return $tasks;
    }

    /**
     * {@inheritdoc}
     */
    public function configureApplyCommand(InputDefinition $definition)
    {
        $definition->addOption(new InputOption('skip-backup', null, InputOption::VALUE_NONE, 'Do not create a backup before applying the patch.'));
        $definition->addOption(new InputOption('limit-backups', null, InputOption::VALUE_REQUIRED, 'Limit the number of backups that are stored'));

        $this->configureCommand($definition);
    }

    /**
     * {@inheritdoc}
     */
    public function rollback($backupDir, $patchDir, $installDir, array $intermediateBackupDirs, array $options)
    {
        $tasks = [];
        $backupFilesDir = $backupDir . '/' . PackageConstants::PATCH_DIR;

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
            $tasks[] = new MigrateDown($backupDir, $patchDir, $installDir, MigrationsConstants::TYPE_DATABASE, (bool) $options['ignore-unavailable-migrations']);
        }

        if (!$options['skip-file-migrations']) {
            $tasks[] = new MigrateDown($backupDir, $patchDir, $installDir, MigrationsConstants::TYPE_FILE, (bool) $options['ignore-unavailable-migrations']);
        }

        $tasks[] = new SetPermissions($backupFilesDir, $installDir);

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
