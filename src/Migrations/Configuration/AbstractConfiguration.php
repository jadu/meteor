<?php

namespace Meteor\Migrations\Configuration;

use Doctrine\Migrations\Exception\DuplicateMigrationVersion;
use Doctrine\Migrations\Exception\MigrationException;
use Doctrine\Migrations\Version\Version;

abstract class AbstractConfiguration extends DoctrineConfiguration
{
    /**
     * Path to the Jadu install.
     *
     * @var string
     */
    private $jaduPath;

    /**
     * @return string
     */
    public function getJaduPath()
    {
        return $this->jaduPath;
    }

    /**
     * @param string $jaduPath
     */
    public function setJaduPath($jaduPath)
    {
        $this->jaduPath = $jaduPath;
    }

    /**
     * Register a single migration version to be executed by a AbstractMigration
     * class.
     *
     * @param string $version The version of the migration in the format YYYYMMDDHHMMSS
     * @param string $class The migration class to execute for the version
     *
     * @return Version
     *
     * @throws MigrationException
     */
    public function registerMigration(string $version, string $class): Version
    {
        if (isset($this->migrationVersions[$version])) {
            throw DuplicateMigrationVersion::new($version, get_class($this->migrationVersions[$version]));
        }

        $version = $this->createMigration($version, $class);
        $this->migrationVersions[$version->getVersion()] = $version;
        ksort($this->migrationVersions);

        return $version;
    }

    /**
     * Create the migration version.
     *
     * @param string $version
     * @param string $class
     *
     * @return Version
     */
    abstract protected function createMigration($version, $class);
}
