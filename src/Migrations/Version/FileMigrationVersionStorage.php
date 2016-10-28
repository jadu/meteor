<?php

namespace Meteor\Migrations\Version;

use Meteor\Migrations\Configuration\FileConfiguration;

class FileMigrationVersionStorage
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var array
     */
    private $versions;

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Checks whether a version has been migrated.
     *
     * @param string $version
     *
     * @return bool
     */
    public function hasVersionMigrated($version)
    {
        $this->loadVersions();

        return in_array((string) $version, $this->versions, true);
    }

    /**
     * @return array
     */
    public function getMigratedVersions()
    {
        $this->loadVersions();

        return $this->versions;
    }

    /**
     * @return int
     */
    public function getNumberOfExecutedMigrations()
    {
        $this->loadVersions();

        return count($this->versions);
    }

    /**
     * @return string
     */
    public function getCurrentVersion()
    {
        $this->loadVersions();

        $versionCount = count($this->versions);
        if ($versionCount === 0) {
            return '0';
        }

        return $this->versions[$versionCount - 1];
    }

    /**
     * @param string $version
     */
    public function markMigrated($version)
    {
        $this->loadVersions();

        $this->versions[] = trim($version);
        $this->normaliseVersions();
        $this->saveVersions();
    }

    /**
     * @param string $version
     */
    public function markNotMigrated($version)
    {
        $this->loadVersions();

        $key = array_search((string) $version, $this->versions, true);
        if ($key !== false) {
            unset($this->versions[$key]);
            $this->normaliseVersions();
            $this->saveVersions();
        }
    }

    /**
     * @return bool
     */
    public function isInitialised()
    {
        return file_exists($this->file);
    }

    /**
     * Initialises the version storage from the current known version.
     *
     * @param FileConfiguration $configuration
     * @param string $currentVersion
     */
    public function initialise(FileConfiguration $configuration, $currentVersion)
    {
        $versions = array_filter($configuration->getMigrations(), function (FileMigrationVersion $version) use ($currentVersion) {
            return $version->getVersion() <= $currentVersion;
        });

        foreach ($versions as $version) {
            $this->markMigrated($version->getVersion());
        }
    }

    private function loadVersions()
    {
        if ($this->versions === null) {
            if (file_exists($this->file)) {
                $this->versions = explode("\n", file_get_contents($this->file));
                $this->normaliseVersions();
            } else {
                $this->versions = [];
            }
        }
    }

    private function saveVersions()
    {
        file_put_contents($this->file, implode("\n", $this->versions));
    }

    private function normaliseVersions()
    {
        // Normalise version strings
        $this->versions = array_map('trim', $this->versions);

        // Ensure versions only appear once
        $this->versions = array_unique($this->versions);

        // Remove empty versions
        $this->versions = array_filter($this->versions);

        // Sort numerically
        sort($this->versions, SORT_NUMERIC);
        $this->versions = array_values($this->versions);
    }
}
