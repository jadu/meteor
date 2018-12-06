<?php

namespace Meteor\Patch\Strategy\Overwrite;

use Meteor\Migrations\MigrationsConstants;
use Meteor\Patch\Task\CheckVersion;

class OverwritePatchStrategyTest extends \PHPUnit_Framework_TestCase
{
    private $strategy;

    public function setUp()
    {
        $this->strategy = new OverwritePatchStrategy();
    }

    public function testApply()
    {
        $tasks = $this->strategy->apply('patch', 'install', [
            'skip-backup' => false,
            'skip-db-migrations' => false,
            'skip-file-migrations' => false,
            'skip-version-check' => false,
            'ignore-unavailable-migrations' => false,
        ]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        $this->assertSame('install', $tasks[0]->targetDir);

        $this->assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        $this->assertSame('patch/to_patch', $tasks[1]->workingDir);
        $this->assertSame('install', $tasks[1]->installDir);

        $this->assertInstanceOf('Meteor\Patch\Task\CheckModuleCmsDependency', $tasks[2]);
        $this->assertSame('patch/to_patch', $tasks[2]->workingDir);
        $this->assertSame('install', $tasks[2]->installDir);

        $this->assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[3]);
        $this->assertSame('patch/to_patch', $tasks[3]->workingDir);
        $this->assertSame('install', $tasks[3]->installDir);
        $this->assertSame(CheckVersion::GREATER_THAN_OR_EQUAL, $tasks[3]->operator);

        $this->assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[4]);
        $this->assertSame('install', $tasks[4]->installDir);

        $this->assertInstanceOf('Meteor\Patch\Task\CheckDiskSpace', $tasks[5]);
        $this->assertSame('install', $tasks[5]->installDir);

        $this->assertInstanceOf('Meteor\Patch\Task\BackupFiles', $tasks[6]);
        $this->assertSame('install/backups/' . date('YmdHis'), $tasks[6]->backupDir);
        $this->assertSame('patch', $tasks[6]->patchDir);
        $this->assertSame('install', $tasks[6]->installDir);

        $this->assertInstanceOf('Meteor\Patch\Task\UpdateMigrationVersionFiles', $tasks[7]);
        $this->assertSame('install/backups/' . date('YmdHis'), $tasks[7]->backupDir);
        $this->assertSame('patch', $tasks[7]->patchDir);
        $this->assertSame('install', $tasks[7]->installDir);

        $this->assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[8]);
        $this->assertSame('patch/to_patch', $tasks[8]->sourceDir);
        $this->assertSame('install', $tasks[8]->targetDir);

        $this->assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[9]);
        $this->assertSame('patch', $tasks[9]->workingDir);
        $this->assertSame('install', $tasks[9]->installDir);
        $this->assertFalse($tasks[9]->ignoreUnavailableMigrations);
        $this->assertSame(MigrationsConstants::TYPE_DATABASE, $tasks[9]->type);

        $this->assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[10]);
        $this->assertSame('patch', $tasks[10]->workingDir);
        $this->assertSame('install', $tasks[10]->installDir);
        $this->assertFalse($tasks[10]->ignoreUnavailableMigrations);
        $this->assertSame(MigrationsConstants::TYPE_FILE, $tasks[10]->type);

        $this->assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[11]);
        $this->assertSame('patch/to_patch', $tasks[11]->sourceDir);
        $this->assertSame('install', $tasks[11]->targetDir);
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

        $this->assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        $this->assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[2]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckDiskSpace', $tasks[3]);
        $this->assertInstanceOf('Meteor\Patch\Task\BackupFiles', $tasks[4]);
        $this->assertInstanceOf('Meteor\Patch\Task\UpdateMigrationVersionFiles', $tasks[5]);
        $this->assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[6]);
        $this->assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[7]);
        $this->assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[8]);
        $this->assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[9]);
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
        $this->assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        $this->assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[2]);
        $this->assertInstanceOf('Meteor\Patch\Task\LimitBackups', $tasks[3]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckDiskSpace', $tasks[4]);
        $this->assertInstanceOf('Meteor\Patch\Task\BackupFiles', $tasks[5]);
        $this->assertInstanceOf('Meteor\Patch\Task\UpdateMigrationVersionFiles', $tasks[6]);
        $this->assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[7]);
        $this->assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[8]);
        $this->assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[9]);
        $this->assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[10]);
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

        $this->assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        $this->assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckModuleCmsDependency', $tasks[2]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[3]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[4]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckDiskSpace', $tasks[5]);
        $this->assertInstanceOf('Meteor\Patch\Task\BackupFiles', $tasks[6]);
        $this->assertInstanceOf('Meteor\Patch\Task\UpdateMigrationVersionFiles', $tasks[7]);
        $this->assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[8]);
        $this->assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[9]);
        $this->assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[10]);
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

        $this->assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        $this->assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckModuleCmsDependency', $tasks[2]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[3]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[4]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckDiskSpace', $tasks[5]);
        $this->assertInstanceOf('Meteor\Patch\Task\BackupFiles', $tasks[6]);
        $this->assertInstanceOf('Meteor\Patch\Task\UpdateMigrationVersionFiles', $tasks[7]);
        $this->assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[8]);
        $this->assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[9]);
        $this->assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[10]);
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

        $this->assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        $this->assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckModuleCmsDependency', $tasks[2]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[3]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckDiskSpace', $tasks[4]);
        $this->assertInstanceOf('Meteor\Patch\Task\BackupFiles', $tasks[5]);
        $this->assertInstanceOf('Meteor\Patch\Task\UpdateMigrationVersionFiles', $tasks[6]);
        $this->assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[7]);
        $this->assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[8]);
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

        $this->assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        $this->assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckModuleCmsDependency', $tasks[2]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[3]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[4]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckDiskSpace', $tasks[5]);
        $this->assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[6]);
        $this->assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[7]);
        $this->assertInstanceOf('Meteor\Patch\Task\MigrateUp', $tasks[8]);
        $this->assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[9]);
    }

    public function testRollback()
    {
        $tasks = $this->strategy->rollback('backups/1', 'patch', 'install', [], [
            'skip-db-migrations' => false,
            'skip-file-migrations' => false,
            'skip-version-check' => false,
            'ignore-unavailable-migrations' => false,
        ]);

        $this->assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        $this->assertSame('install', $tasks[0]->targetDir);

        $this->assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        $this->assertSame('backups/1/to_patch', $tasks[1]->workingDir);
        $this->assertSame('install', $tasks[1]->installDir);

        $this->assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[2]);
        $this->assertSame('backups/1/to_patch', $tasks[2]->workingDir);
        $this->assertSame('install', $tasks[2]->installDir);
        $this->assertSame(CheckVersion::LESS_THAN_OR_EQUAL, $tasks[2]->operator);

        $this->assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[3]);
        $this->assertSame('install', $tasks[3]->installDir);

        $this->assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[4]);
        $this->assertSame('backups/1/to_patch', $tasks[4]->sourceDir);
        $this->assertSame('install', $tasks[4]->targetDir);

        $this->assertInstanceOf('Meteor\Patch\Task\MigrateDown', $tasks[5]);
        $this->assertSame('backups/1', $tasks[5]->backupDir);
        $this->assertSame('patch', $tasks[5]->workingDir);
        $this->assertSame('install', $tasks[5]->installDir);
        $this->assertFalse($tasks[5]->ignoreUnavailableMigrations);
        $this->assertSame(MigrationsConstants::TYPE_DATABASE, $tasks[5]->type);

        $this->assertInstanceOf('Meteor\Patch\Task\MigrateDown', $tasks[6]);
        $this->assertSame('backups/1', $tasks[6]->backupDir);
        $this->assertSame('patch', $tasks[6]->workingDir);
        $this->assertSame('install', $tasks[6]->installDir);
        $this->assertFalse($tasks[6]->ignoreUnavailableMigrations);
        $this->assertSame(MigrationsConstants::TYPE_FILE, $tasks[6]->type);

        $this->assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[7]);
        $this->assertSame('backups/1/to_patch', $tasks[7]->sourceDir);
        $this->assertSame('install', $tasks[7]->targetDir);

        $this->assertInstanceOf('Meteor\Patch\Task\DeleteBackup', $tasks[8]);
        $this->assertSame('backups/1', $tasks[8]->backupDir);
    }

    public function testRollbackCanSkipDbMigrations()
    {
        $tasks = $this->strategy->rollback('backups/1', 'patch', 'install', [], [
            'skip-db-migrations' => true,
            'skip-file-migrations' => false,
            'skip-version-check' => false,
            'ignore-unavailable-migrations' => false,
        ]);

        $this->assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        $this->assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[2]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[3]);
        $this->assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[4]);
        $this->assertInstanceOf('Meteor\Patch\Task\MigrateDown', $tasks[5]);
        $this->assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[6]);
        $this->assertInstanceOf('Meteor\Patch\Task\DeleteBackup', $tasks[7]);
    }

    public function testRollbackCanSkipFileMigrations()
    {
        $tasks = $this->strategy->rollback('backups/1', 'patch', 'install', [], [
            'skip-db-migrations' => false,
            'skip-file-migrations' => true,
            'skip-version-check' => false,
            'ignore-unavailable-migrations' => false,
        ]);

        $this->assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        $this->assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[2]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[3]);
        $this->assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[4]);
        $this->assertInstanceOf('Meteor\Patch\Task\MigrateDown', $tasks[5]);
        $this->assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[6]);
        $this->assertInstanceOf('Meteor\Patch\Task\DeleteBackup', $tasks[7]);
    }

    public function testRollbackCanSkipDbAndFileMigrations()
    {
        $tasks = $this->strategy->rollback('backups/1', 'patch', 'install', [], [
            'skip-db-migrations' => true,
            'skip-file-migrations' => true,
            'skip-version-check' => false,
            'ignore-unavailable-migrations' => false,
        ]);

        $this->assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        $this->assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckVersion', $tasks[2]);
        $this->assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[3]);
        $this->assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[4]);
        $this->assertInstanceOf('Meteor\Patch\Task\DeleteBackup', $tasks[5]);
    }

    public function testRollbackCanSkipVersionCheck()
    {
        $tasks = $this->strategy->rollback('backups/1', 'patch', 'install', [], [
            'skip-db-migrations' => false,
            'skip-file-migrations' => false,
            'skip-version-check' => true,
            'ignore-unavailable-migrations' => false,
        ]);

        $this->assertInstanceOf('Meteor\Patch\Task\CheckWritePermission', $tasks[0]);
        $this->assertInstanceOf('Meteor\Patch\Task\DisplayVersionInfo', $tasks[1]);
        $this->assertInstanceOf('Meteor\Patch\Task\CheckDatabaseConnection', $tasks[2]);
        $this->assertInstanceOf('Meteor\Patch\Task\CopyFiles', $tasks[3]);
        $this->assertInstanceOf('Meteor\Patch\Task\MigrateDown', $tasks[4]);
        $this->assertInstanceOf('Meteor\Patch\Task\MigrateDown', $tasks[5]);
        $this->assertInstanceOf('Meteor\Patch\Task\SetPermissions', $tasks[6]);
        $this->assertInstanceOf('Meteor\Patch\Task\DeleteBackup', $tasks[7]);
    }

    public function testRollbackDeletesIntermediateBackups()
    {
        $tasks = $this->strategy->rollback('backups/3', 'patch', 'install', ['backups/1', 'backups/2'], [
            'skip-db-migrations' => false,
            'skip-file-migrations' => false,
            'skip-version-check' => false,
            'ignore-unavailable-migrations' => false,
        ]);

        $this->assertInstanceOf('Meteor\Patch\Task\DeleteBackup', $tasks[8]);
        $this->assertSame('backups/1', $tasks[8]->backupDir);

        $this->assertInstanceOf('Meteor\Patch\Task\DeleteBackup', $tasks[9]);
        $this->assertSame('backups/2', $tasks[9]->backupDir);

        $this->assertInstanceOf('Meteor\Patch\Task\DeleteBackup', $tasks[10]);
        $this->assertSame('backups/3', $tasks[10]->backupDir);
    }
}
