<?php

namespace Meteor\Migrations\Configuration;

use Doctrine\Migrations\Version\Version;
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
     *  @return Version[]
     */
    public function registerMigrationsFromDirectory(string $path): array
    {
        $this->validate();

        $migrations = $this->getDependencyFactory()->getRecursiveRegexFinder()->findMigrations(
            $path,
            $this->getMigrationsNamespace()
        );

        $versions = [];

        foreach ($migrations as $version => $class) {
            if (!class_exists($class)) {
                throw MigrationClassNotFound::new($class, $this->getMigrationsNamespace());
            }

            if (isset($versions[$version])) {
                throw DuplicateMigrationVersion::new($version, get_class($versions[$version]));
            }

            $version = new FileMigrationVersion(
                $this,
                $version,
                $class,
                $this->getDependencyFactory()->getVersionExecutor(),
                $this->versionStorage
            );

            $versions[] = $version;
        }

        return $versions;
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
    public function hasVersionMigrated(Version $version): bool
    {
        return $this->versionStorage->hasVersionMigrated($version->getVersion());
    }

    /**
     * {@inheritdoc}
     */
    public function getMigratedVersions(): array
    {
        return $this->versionStorage->getMigratedVersions();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentVersion(): string
    {
        return $this->versionStorage->getCurrentVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function getNumberOfExecutedMigrations(): int
    {
        return $this->versionStorage->getNumberOfExecutedMigrations();
    }

    /**
     * {@inheritdoc}
     */
    public function createMigrationTable(): bool
    {
        // Migrations table is not needed for file migrations
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function createMigration($version, $class)
    {
        return new FileMigrationVersion(
            $this,
            $version,
            $class,
            $this->getDependencyFactory()->getVersionExecutor(),
            $this->versionStorage
        );
    }
}
