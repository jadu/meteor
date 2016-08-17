<?php

namespace Meteor\Migrations\Configuration;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\MigrationException;
use Doctrine\DBAL\Migrations\Version;

/**
 * The base Configuration class stored migrations in a private $migrations property that cannot be accesed.
 * To overcome this limitation the methods that utilise this property have been overridden and made to use
 * the $migrationVersions property instead. All of the methods here were taken from the Configuration class
 * so if Doctrine Migrations is updated these methods should also be.
 *
 * @codeCoverageIgnore Tested by Doctrine and copy and pasted by TG
 */
abstract class DoctrineConfiguration extends Configuration
{
    /**
     * Array of the registered migrations.
     *
     * @var Version[]
     */
    protected $migrationVersions = array();

    /**
     * Get the array of registered migration versions.
     *
     * @return Version[] $migrations
     */
    public function getMigrations()
    {
        return $this->migrationVersions;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion($version)
    {
        if (!isset($this->migrationVersions[$version])) {
            throw MigrationException::unknownMigrationVersion($version);
        }

        return $this->migrationVersions[$version];
    }

    /**
     * {@inheritdoc}
     */
    public function hasVersion($version)
    {
        return isset($this->migrationVersions[$version]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableVersions()
    {
        $availableVersions = array();
        foreach ($this->migrationVersions as $migration) {
            $availableVersions[] = $migration->getVersion();
        }

        return $availableVersions;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentVersion()
    {
        $this->createMigrationTable();

        $where = null;
        if ($this->migrationVersions) {
            $migratedVersions = array();
            foreach ($this->migrationVersions as $migration) {
                $migratedVersions[] = sprintf("'%s'", $migration->getVersion());
            }
            $where = ' WHERE version IN ('.implode(', ', $migratedVersions).')';
        }

        $sql = sprintf('SELECT version FROM %s%s ORDER BY version DESC',
            $this->getMigrationsTableName(), $where
        );

        $sql = $this->getConnection()->getDatabasePlatform()->modifyLimitQuery($sql, 1);
        $result = $this->getConnection()->fetchColumn($sql);

        return $result !== false ? (string) $result : '0';
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativeVersion($version, $delta)
    {
        $versions = array_keys($this->migrationVersions);
        array_unshift($versions, 0);
        $offset = array_search($version, $versions, true);
        if ($offset === false || !isset($versions[$offset + $delta])) {
            // Unknown version or delta out of bounds.
            return;
        }

        return (string) $versions[$offset + $delta];
    }

    /**
     * {@inheritdoc}
     */
    public function getNumberOfAvailableMigrations()
    {
        return count($this->migrationVersions);
    }

    /**
     * {@inheritdoc}
     */
    public function getLatestVersion()
    {
        $versions = array_keys($this->migrationVersions);
        $latest = end($versions);

        return $latest !== false ? (string) $latest : '0';
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationsToExecute($direction, $to)
    {
        if ($direction === 'down') {
            if (count($this->migrationVersions)) {
                $allVersions = array_reverse(array_keys($this->migrationVersions));
                $classes = array_reverse(array_values($this->migrationVersions));
                $allVersions = array_combine($allVersions, $classes);
            } else {
                $allVersions = array();
            }
        } else {
            $allVersions = $this->migrationVersions;
        }
        $versions = array();
        $migrated = $this->getMigratedVersions();
        foreach ($allVersions as $version) {
            if ($this->shouldMigrationBeExecuted($direction, $version, $to, $migrated)) {
                $versions[$version->getVersion()] = $version;
            }
        }

        return $versions;
    }

    /**
     * {@inheritdoc}
     */
    private function shouldMigrationBeExecuted($direction, Version $version, $to, $migrated)
    {
        if ($direction === 'down') {
            if (!in_array($version->getVersion(), $migrated, true)) {
                return false;
            }

            return $version->getVersion() > $to;
        }

        if ($direction === 'up') {
            if (in_array($version->getVersion(), $migrated, true)) {
                return false;
            }

            return $version->getVersion() <= $to;
        }
    }
}
