<?php

namespace Meteor\Migrations\Version;

use org\bovigo\vfs\vfsStream;

class FileMigrationVersionStorageTest extends \PHPUnit_Framework_TestCase
{
    private $versionStorage;

    public function setUp()
    {
        vfsStream::setup('root');

        $this->versionStorage = new FileMigrationVersionStorage(vfsStream::url('root/jadumigrations'));
    }

    public function testIsInitialisedReturnsFalseWhenFileDoesNotExist()
    {
        $this->assertFalse($this->versionStorage->isInitialised());
    }

    public function testIsInitialisedReturnsTrueWhenFileExists()
    {
        vfsStream::setup('root', null, array(
            'jadumigrations' => "1\n2\n3",
        ));

        $this->assertTrue($this->versionStorage->isInitialised());
    }

    public function testHasVersionMigratedReturnsTrueWhenMigrated()
    {
        vfsStream::setup('root', null, array(
            'jadumigrations' => "1\n2\n3",
        ));

        $this->assertTrue($this->versionStorage->hasVersionMigrated(2));
    }

    public function testHasVersionMigratedReturnsFalseWhenNotMigrated()
    {
        vfsStream::setup('root', null, array(
            'jadumigrations' => "1\n2\n3",
        ));

        $this->assertFalse($this->versionStorage->hasVersionMigrated(5));
    }

    public function testGetMigratedVersions()
    {
        vfsStream::setup('root', null, array(
            'jadumigrations' => "1\n2\n3",
        ));

        $this->assertSame(array('1', '2', '3'), $this->versionStorage->getMigratedVersions());
    }

    public function testGetMigratedVersionsSortsVersions()
    {
        vfsStream::setup('root', null, array(
            'jadumigrations' => "2\n3\n1",
        ));

        $this->assertSame(array('1', '2', '3'), $this->versionStorage->getMigratedVersions());
    }

    public function testGetMigratedVersionsRemovesDuplicates()
    {
        vfsStream::setup('root', null, array(
            'jadumigrations' => "1\n2\n3\n2\n3\n1",
        ));

        $this->assertSame(array('1', '2', '3'), $this->versionStorage->getMigratedVersions());
    }

    public function testGetMigratedVersionsNormalisesVersionsFromFile()
    {
        vfsStream::setup('root', null, array(
            'jadumigrations' => "1   \n   2\n3",
        ));

        $this->assertSame(array('1', '2', '3'), $this->versionStorage->getMigratedVersions());
    }

    public function testGetNumberOfExecutedMigrations()
    {
        vfsStream::setup('root', null, array(
            'jadumigrations' => "1\n2\n3",
        ));

        $this->assertSame(3, $this->versionStorage->getNumberOfExecutedMigrations());
    }

    public function testGetCurrentVersionReturnsLatestVersion()
    {
        vfsStream::setup('root', null, array(
            'jadumigrations' => "1\n2\n3",
        ));

        $this->assertSame('3', $this->versionStorage->getCurrentVersion());
    }

    public function testGetCurrentVersionSortsVersions()
    {
        vfsStream::setup('root', null, array(
            'jadumigrations' => "2\n3\n1",
        ));

        $this->assertSame('3', $this->versionStorage->getCurrentVersion());
    }

    public function testGetCurrentVersionReturnsZeroWhenFileIsEmpty()
    {
        vfsStream::setup('root', null, array(
            'jadumigrations' => '',
        ));

        $this->assertSame('0', $this->versionStorage->getCurrentVersion());
    }

    public function testMarkMigrated()
    {
        vfsStream::setup('root', null, array(
            'jadumigrations' => "1\n2\n3",
        ));

        $this->versionStorage->markMigrated(4);

        $this->assertSame("1\n2\n3\n4", file_get_contents(vfsStream::url('root/jadumigrations')));
    }

    public function testMarkMigratedOnlyAddsOnce()
    {
        vfsStream::setup('root', null, array(
            'jadumigrations' => "1\n2\n3",
        ));

        $this->versionStorage->markMigrated(4);
        $this->versionStorage->markMigrated(4);
        $this->versionStorage->markMigrated(4);

        $this->assertSame("1\n2\n3\n4", file_get_contents(vfsStream::url('root/jadumigrations')));
    }

    public function testMarkMigratedAddsInOrder()
    {
        vfsStream::setup('root', null, array(
            'jadumigrations' => "1\n3",
        ));

        $this->versionStorage->markMigrated(2);

        $this->assertSame("1\n2\n3", file_get_contents(vfsStream::url('root/jadumigrations')));
    }

    public function testMarkMigratedAddsToNewFile()
    {
        vfsStream::setup('root');

        $this->versionStorage->markMigrated(2);

        $this->assertSame('2', file_get_contents(vfsStream::url('root/jadumigrations')));
    }

    public function testMarkNotMigrated()
    {
        vfsStream::setup('root', null, array(
            'jadumigrations' => "1\n2\n3",
        ));

        $this->versionStorage->markNotMigrated(2);

        $this->assertSame("1\n3", file_get_contents(vfsStream::url('root/jadumigrations')));
    }

    public function testMarkNotMigratedDoesNothingWhenVersionNotPresentInFile()
    {
        vfsStream::setup('root', null, array(
            'jadumigrations' => "1\n2\n3",
        ));

        $this->versionStorage->markNotMigrated(4);

        $this->assertSame("1\n2\n3", file_get_contents(vfsStream::url('root/jadumigrations')));
    }
}
