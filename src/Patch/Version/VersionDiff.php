<?php

namespace Meteor\Patch\Version;

use Composer\Semver\Comparator;

class VersionDiff
{
    /**
     * @var string
     */
    private $packageName;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $newVersion;

    /**
     * @var string
     */
    private $currentVersion;

    /**
     * @param string $fileName
     * @param string $newVersion
     * @param string $currentVersion
     */
    public function __construct($packageName, $fileName, $newVersion, $currentVersion)
    {
        $this->packageName = $packageName;
        $this->fileName = $fileName;
        $this->newVersion = $newVersion;
        $this->currentVersion = $currentVersion;
    }

    /**
     * @return string
     */
    public function getPackageName()
    {
        return $this->packageName;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getNewVersion()
    {
        return $this->newVersion;
    }

    /**
     * @return string
     */
    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    /**
     * @return bool
     */
    public function isLessThan()
    {
        return Comparator::lessThan($this->newVersion, $this->currentVersion);
    }

    /**
     * @return bool
     */
    public function isGreaterThan()
    {
        return Comparator::greaterThan($this->newVersion, $this->currentVersion);
    }
}
