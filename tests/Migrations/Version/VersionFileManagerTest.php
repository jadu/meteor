<?php

namespace Meteor\Migrations\Version;

use org\bovigo\vfs\vfsStream;

class VersionFileManagerTest extends \PHPUnit_Framework_TestCase
{
    private $versionFileManager;

    public function setUp()
    {
        $this->versionFileManager = new VersionFileManager();
    }

    /**
     * @dataProvider getCurrentVersionProvider
     */
    public function testGetCurrentVersion(array $structure, $table, $versionFilename, $expectedVersion)
    {
        vfsStream::setup('root', null, $structure);

        $this->assertSame($expectedVersion, $this->versionFileManager->getCurrentVersion(
            vfsStream::url('root'),
            $table,
            $versionFilename
        ));
    }

    public function getCurrentVersionProvider()
    {
        return [
            [
                ['MIGRATION_NUMBER' => '20160701102030'],
                'JaduMigrations',
                VersionFileManager::DATABASE_MIGRATION,
                '20160701102030',
            ],
            [
                [],
                'JaduMigrations',
                VersionFileManager::DATABASE_MIGRATION,
                '0',
            ],
            [
                ['XFP_MIGRATION_NUMBER' => '20160701102030'],
                'JaduMigrationsXFP',
                VersionFileManager::DATABASE_MIGRATION,
                '20160701102030',
            ],
            [
                [],
                'JaduMigrationsXFP',
                VersionFileManager::DATABASE_MIGRATION,
                '0',
            ],
            [
                ['SPACECRAFTCUSTOMMIGRATIONS_MIGRATION_NUMBER' => '20160701102030'],
                'SpacecraftCustomMigrations',
                VersionFileManager::DATABASE_MIGRATION,
                '20160701102030',
            ],
            [
                [],
                'SpacecraftCustomMigrations',
                VersionFileManager::DATABASE_MIGRATION,
                '0',
            ],
            [
                ['FILE_SYSTEM_MIGRATION_NUMBER' => '20160701102030'],
                'JaduMigrations',
                VersionFileManager::FILE_MIGRATION,
                '20160701102030',
            ],
            [
                [],
                'JaduMigrations',
                VersionFileManager::FILE_MIGRATION,
                '0',
            ],
            [
                ['XFP_FILE_SYSTEM_MIGRATION_NUMBER' => '20160701102030'],
                'JaduMigrationsXFP',
                VersionFileManager::FILE_MIGRATION,
                '20160701102030',
            ],
            [
                [],
                'JaduMigrationsXFP',
                VersionFileManager::FILE_MIGRATION,
                '0',
            ],
            [
                ['SPACECRAFTCUSTOMMIGRATIONS_FILE_SYSTEM_MIGRATION_NUMBER' => '20160701102030'],
                'SpacecraftCustomMigrations',
                VersionFileManager::FILE_MIGRATION,
                '20160701102030',
            ],
            [
                [],
                'SpacecraftCustomMigrations',
                VersionFileManager::FILE_MIGRATION,
                '0',
            ],
        ];
    }

    /**
     * @dataProvider setCurrentVersionProvider
     */
    public function testSetCurrentVersion($table, $versionFilename, $expectedVersionFileName)
    {
        vfsStream::setup('root');

        $this->versionFileManager->setCurrentVersion('20160601102030', vfsStream::url('root'), $table, $versionFilename);

        $this->assertSame('20160601102030', file_get_contents(vfsStream::url('root/'.$expectedVersionFileName)));
    }

    public function setCurrentVersionProvider()
    {
        return [
            [
                'JaduMigrations',
                VersionFileManager::DATABASE_MIGRATION,
                'MIGRATION_NUMBER',
            ],
            [
                'JaduMigrationsXFP',
                VersionFileManager::DATABASE_MIGRATION,
                'XFP_MIGRATION_NUMBER',
            ],
            [
                'SpacecraftCustomMigrations',
                VersionFileManager::DATABASE_MIGRATION,
                'SPACECRAFTCUSTOMMIGRATIONS_MIGRATION_NUMBER',
            ],
            [
                'JaduMigrations',
                VersionFileManager::FILE_MIGRATION,
                'FILE_SYSTEM_MIGRATION_NUMBER',
            ],
            [
                'JaduMigrationsXFP',
                VersionFileManager::FILE_MIGRATION,
                'XFP_FILE_SYSTEM_MIGRATION_NUMBER',
            ],
            [
                'SpacecraftCustomMigrations',
                VersionFileManager::FILE_MIGRATION,
                'SPACECRAFTCUSTOMMIGRATIONS_FILE_SYSTEM_MIGRATION_NUMBER',
            ],
        ];
    }
}
