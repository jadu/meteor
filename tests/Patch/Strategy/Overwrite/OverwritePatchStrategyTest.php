<?php

namespace Meteor\Patch\Strategy\Overwrite;

use Meteor\Migrations\MigrationsConstants;
use Meteor\Patch\Task\CheckVersion;
use PHPUnit\Framework\TestCase;

class OverwritePatchStrategyTest extends TestCase
{
    private $strategy;

    protected function setUp(): void
    {
        $this->strategy = new OverwritePatchStrategy();
    }

    public function testApply()
    {
        $time = date('YmdHis');
        $tasks = $this->strategy->apply('patch', 'install', [
            'skip-backup' => false,
            'skip-db-migrations' => false,
            'skip-file-migrations' => false,
            'skip-version-check' => false,
            'ignore-unavailable-migrations' => false,
        ]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        static::assertSame('install', $tasks[0]->targetDir);

        static::assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        static::assertSame('patch/to_patch', $tasks[1]->workingDir);
        static::assertSame('install', $tasks[1]->installDir);

        static::assertInstanceOf('Meteor\Patch\Task\CheckModuleCmsDependency', $tasks[2]);
        static::assertSame('patch/to_patch', $tasks[2]->workingDir);
        static::assertSame('install', $tasks[2]->installDir);

        static::assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[3]);
        static::assertSame('patch/to_patch', $tasks[3]->workingDir);
        static::assertSame('install', $tasks[3]->installDir);
        static::assertSame(CheckVersion::GREATER_THAN_OR_EQUAL, $tasks[3]->operator);

        static::assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[4]);
        static::assertSame('install', $tasks[4]->installDir);

        static::assertInstanceOf('Meteor\Patch\Task\CheckDiskSpace', $tasks[5]);
        static::assertSame('install', $tasks[5]->installDir);

        static::assertInstanceOf('Meteor\Patch\Task\BackupFiles', $tasks[6]);
        static::assertSame('install/backups/' . $time, $tasks[6]->backupDir);
        static::assertSame('patch', $tasks[6]->patchDir);
        static::assertSame('install', $tasks[6]->installDir);

        static::assertInstanceOf('Meteor\Patch\Task\UpdateMigrationVersionFiles', $tasks[7]);
        static::assertSame('install/backups/' . date('YmdHis'), $tasks[7]->backupDir);
        static::assertSame('patch', $tasks[7]->patchDir);
        static::assertSame('install', $tasks[7]->installDir);

        static::assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[8]);
        static::assertSame('patch/to_patch', $tasks[8]->sourceDir);
        static::assertSame('install', $tasks[8]->targetDir);

        static::assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[9]);
        static::assertSame('patch', $tasks[9]->workingDir);
        static::assertSame('install', $tasks[9]->installDir);
        static::assertFalse($tasks[9]->ignoreUnavailableMigrations);
        static::assertSame(MigrationsConstants::TYPE_DATABASE, $tasks[9]->type);

        static::assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[10]);
        static::assertSame('patch', $tasks[10]->workingDir);
        static::assertSame('install', $tasks[10]->installDir);
        static::assertFalse($tasks[10]->ignoreUnavailableMigrations);
        static::assertSame(MigrationsConstants::TYPE_FILE, $tasks[10]->type);

        static::assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[11]);
        static::assertSame('patch/to_patch', $tasks[11]->sourceDir);
        static::assertSame('install', $tasks[11]->targetDir);
    }

    public function testApplyCanSkipVersionCheck()
    {
        $tasks = $this->strategy->apply('patch', 'install', [
            'skip-backup' => false,
            'skip-db-migrations' => false,
            'skip-file-migrations' => false,
            'skip-version-check' => true,
            'ignore-unavailable-migrations' => false,
        ]);

        static::assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        static::assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[2]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckDiskSpace', $tasks[3]);
        static::assertInstanceOf('Meteor\Patch\Task\BackupFiles', $tasks[4]);
        static::assertInstanceOf('Meteor\Patch\Task\UpdateMigrationVersionFiles', $tasks[5]);
        static::assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[6]);
        static::assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[7]);
        static::assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[8]);
        static::assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[9]);
    }

    public function testApplyCanLimitBackups()
    {
        $tasks = $this->strategy->apply('patch', 'install', [
            'limit-backups' => 5,
            'skip-backup' => false,
            'skip-db-migrations' => false,
            'skip-file-migrations' => false,
            'skip-version-check' => true,
            'ignore-unavailable-migrations' => false,
        ]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        static::assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[2]);
        static::assertInstanceOf('Meteor\Patch\Task\LimitBackups', $tasks[3]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckDiskSpace', $tasks[4]);
        static::assertInstanceOf('Meteor\Patch\Task\BackupFiles', $tasks[5]);
        static::assertInstanceOf('Meteor\Patch\Task\UpdateMigrationVersionFiles', $tasks[6]);
        static::assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[7]);
        static::assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[8]);
        static::assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[9]);
        static::assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[10]);
    }

    public function testApplyCanSkipFileMigrations()
    {
        $tasks = $this->strategy->apply('patch', 'install', [
            'skip-backup' => false,
            'skip-db-migrations' => false,
            'skip-file-migrations' => true,
            'skip-version-check' => false,
            'ignore-unavailable-migrations' => false,
        ]);

        static::assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        static::assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckModuleCmsDependency', $tasks[2]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[3]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[4]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckDiskSpace', $tasks[5]);
        static::assertInstanceOf('Meteor\Patch\Task\BackupFiles', $tasks[6]);
        static::assertInstanceOf('Meteor\Patch\Task\UpdateMigrationVersionFiles', $tasks[7]);
        static::assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[8]);
        static::assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[9]);
        static::assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[10]);
    }

    public function testApplyCanSkipDbMigrations()
    {
        $tasks = $this->strategy->apply('patch', 'install', [
            'skip-backup' => false,
            'skip-db-migrations' => true,
            'skip-file-migrations' => false,
            'skip-version-check' => false,
            'ignore-unavailable-migrations' => false,
        ]);

        static::assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        static::assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckModuleCmsDependency', $tasks[2]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[3]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[4]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckDiskSpace', $tasks[5]);
        static::assertInstanceOf('Meteor\Patch\Task\BackupFiles', $tasks[6]);
        static::assertInstanceOf('Meteor\Patch\Task\UpdateMigrationVersionFiles', $tasks[7]);
        static::assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[8]);
        static::assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[9]);
        static::assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[10]);
    }

    public function testApplyCanSkipDbAndFileMigrations()
    {
        $tasks = $this->strategy->apply('patch', 'install', [
            'skip-backup' => false,
            'skip-db-migrations' => true,
            'skip-file-migrations' => true,
            'skip-version-check' => false,
            'ignore-unavailable-migrations' => false,
        ]);

        static::assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        static::assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckModuleCmsDependency', $tasks[2]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[3]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckDiskSpace', $tasks[4]);
        static::assertInstanceOf('Meteor\Patch\Task\BackupFiles', $tasks[5]);
        static::assertInstanceOf('Meteor\Patch\Task\UpdateMigrationVersionFiles', $tasks[6]);
        static::assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[7]);
        static::assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[8]);
    }

    public function testApplyCanSkipBackup()
    {
        $tasks = $this->strategy->apply('patch', 'install', [
            'skip-backup' => true,
            'skip-db-migrations' => false,
            'skip-file-migrations' => false,
            'skip-version-check' => false,
            'ignore-unavailable-migrations' => false,
        ]);

        static::assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        static::assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckModuleCmsDependency', $tasks[2]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[3]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[4]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckDiskSpace', $tasks[5]);
        static::assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[6]);
        static::assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[7]);
        static::assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[8]);
        static::assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[9]);
    }

    public function testRollback()
    {
        $tasks = $this->strategy->rollback('backups/1', 'patch', 'install', [], [
            'skip-db-migrations' => false,
            'skip-file-migrations' => false,
            'skip-version-check' => false,
            'ignore-unavailable-migrations' => false,
        ]);

        static::assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        static::assertSame('install', $tasks[0]->targetDir);

        static::assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        static::assertSame('backups/1/to_patch', $tasks[1]->workingDir);
        static::assertSame('install', $tasks[1]->installDir);

        static::assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[2]);
        static::assertSame('backups/1/to_patch', $tasks[2]->workingDir);
        static::assertSame('install', $tasks[2]->installDir);
        static::assertSame(CheckVersion::LESS_THAN_OR_EQUAL, $tasks[2]->operator);

        static::assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[3]);
        static::assertSame('install', $tasks[3]->installDir);

        static::assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[4]);
        static::assertSame('backups/1/to_patch', $tasks[4]->sourceDir);
        static::assertSame('install', $tasks[4]->targetDir);

        static::assertInstanceOf('Meteor\Patch\Task\MigrateDown', $tasks[5]);
        static::assertSame('backups/1', $tasks[5]->backupDir);
        static::assertSame('patch', $tasks[5]->workingDir);
        static::assertSame('install', $tasks[5]->installDir);
        static::assertFalse($tasks[5]->ignoreUnavailableMigrations);
        static::assertSame(MigrationsConstants::TYPE_DATABASE, $tasks[5]->type);

        static::assertInstanceOf('Meteor\Patch\Task\MigrateDown', $tasks[6]);
        static::assertSame('backups/1', $tasks[6]->backupDir);
        static::assertSame('patch', $tasks[6]->workingDir);
        static::assertSame('install', $tasks[6]->installDir);
        static::assertFalse($tasks[6]->ignoreUnavailableMigrations);
        static::assertSame(MigrationsConstants::TYPE_FILE, $tasks[6]->type);

        static::assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[7]);
        static::assertSame('backups/1/to_patch', $tasks[7]->sourceDir);
        static::assertSame('install', $tasks[7]->targetDir);

        static::assertInstanceOf('Meteor\Patch\Task\DeleteBackup', $tasks[8]);
        static::assertSame('backups/1', $tasks[8]->backupDir);
    }

    public function testRollbackCanSkipDbMigrations()
    {
        $tasks = $this->strategy->rollback('backups/1', 'patch', 'install', [], [
            'skip-db-migrations' => true,
            'skip-file-migrations' => false,
            'skip-version-check' => false,
            'ignore-unavailable-migrations' => false,
        ]);

        static::assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        static::assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[2]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[3]);
        static::assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[4]);
        static::assertInstanceOf('Meteor\Patch\Task\MigrateDown', $tasks[5]);
        static::assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[6]);
        static::assertInstanceOf('Meteor\Patch\Task\DeleteBackup', $tasks[7]);
    }

    public function testRollbackCanSkipFileMigrations()
    {
        $tasks = $this->strategy->rollback('backups/1', 'patch', 'install', [], [
            'skip-db-migrations' => false,
            'skip-file-migrations' => true,
            'skip-version-check' => false,
            'ignore-unavailable-migrations' => false,
        ]);

        static::assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        static::assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[2]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[3]);
        static::assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[4]);
        static::assertInstanceOf('Meteor\Patch\Task\MigrateDown', $tasks[5]);
        static::assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[6]);
        static::assertInstanceOf('Meteor\Patch\Task\DeleteBackup', $tasks[7]);
    }

    public function testRollbackCanSkipDbAndFileMigrations()
    {
        $tasks = $this->strategy->rollback('backups/1', 'patch', 'install', [], [
            'skip-db-migrations' => true,
            'skip-file-migrations' => true,
            'skip-version-check' => false,
            'ignore-unavailable-migrations' => false,
        ]);

        static::assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        static::assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[2]);
        static::assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[3]);
        static::assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[4]);
        static::assertInstanceOf('Meteor\Patch\Task\DeleteBackup', $tasks[5]);
    }

    public function testRollbackCanSkipVersionCheck()
    {
        $tasks = $this->strategy->rollback('backups/1', 'patch', 'install', [], [
            'skip-db-migrations' => false,
            'skip-file-migrations' => false,
            'skip-version-check' => true,
            'ignore-unavailable-migrations' => false,
        ]);

        static::assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        static::assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        static::assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[2]);
        static::assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[3]);
        static::assertInstanceOf('Meteor\Patch\Task\MigrateDown', $tasks[4]);
        static::assertInstanceOf('Meteor\Patch\Task\MigrateDown', $tasks[5]);
        static::assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[6]);
        static::assertInstanceOf('Meteor\Patch\Task\DeleteBackup', $tasks[7]);
    }

    public function testRollbackDeletesIntermediateBackups()
    {
        $tasks = $this->strategy->rollback('backups/3', 'patch', 'install', ['backups/1', 'backups/2'], [
            'skip-db-migrations' => false,
            'skip-file-migrations' => false,
            'skip-version-check' => false,
            'ignore-unavailable-migrations' => false,
        ]);

        static::assertInstanceOf('Meteor\Patch\Task\DeleteBackup', $tasks[8]);
        static::assertSame('backups/1', $tasks[8]->backupDir);

        static::assertInstanceOf('Meteor\Patch\Task\DeleteBackup', $tasks[9]);
        static::assertSame('backups/2', $tasks[9]->backupDir);

        static::assertInstanceOf('Meteor\Patch\Task\DeleteBackup', $tasks[10]);
        static::assertSame('backups/3', $tasks[10]->backupDir);
    }
}
