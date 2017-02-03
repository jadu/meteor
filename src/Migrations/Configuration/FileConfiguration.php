<?php

namespace Meteor\Migrations\Configuration;

use Doctrine\DBAL\Migrations\Version;
use Meteor\Migrations\Version\FileMigrationVersion;
use Meteor\Migrations\Version\FileMigrationVersionStorage;

class FileConfiguration extends AbstractConfiguration implements JaduPathAwareConfigurationInterface
{
    const MIGRATION_DIRECTORY = 'filesystem';

    /**
     * @var FileMigrationVersionStorage
     */
    private $versionStorage;

    /**
     * @return FileMigrationVersionStorage
     */
    public function getVersionStorage()
    {
        return $this->versionStorage;
    }

    /**
     * @param FileMigrationVersionStorage $versionStorage
     */
    public function setVersionStorage(FileMigrationVersionStorage $versionStorage)
    {
        $this->versionStorage = $versionStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function hasVersionMigrated(Version $version)
    {
        return $this->versionStorage->hasVersionMigrated($version->getVersion());
    }

    /**
     * {@inheritdoc}
     */
    public function getMigratedVersions()
    {
        return $this->versionStorage->getMigratedVersions();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentVersion()
    {
        return $this->versionStorage->getCurrentVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function getNumberOfExecutedMigrations()
    {
        return $this->versionStorage->getNumberOfExecutedMigrations();
    }

    /**
     * {@inheritdoc}
     */
    public function createMigrationTable()
    {
        // Migrations table is not needed for file migrations
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function createMigration($version, $class)
    {
        return new FileMigrationVersion($this, $version, $class, $this->versionStorage);
    }
}
