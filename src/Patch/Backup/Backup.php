<?php

namespace Meteor\Patch\Backup;

use DateTime;

class Backup
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var array
     */
    private $versions;

    /**
     * @param string $path
     * @param array $versions
     */
    public function __construct($path, array $versions)
    {
        $this->path = $path;
        $this->versions = $versions;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        if ($this->date === null) {
            // Lazily initialise  the date from the path
            $this->date = new DateTime();
            if (preg_match('/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', basename($this->getPath()), $matches)) {
                $this->date->setDate($matches[1], $matches[2], $matches[3]);
                $this->date->setTime($matches[4], $matches[5], $matches[6]);
            }
        }

        return $this->date;
    }

    /**
     * @return array
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        foreach ($this->getVersions() as $version) {
            // if we have a development package we do not want to error on version checks
            if (strpos($version->getNewVersion(), 'dev-') === 0 || strpos($version->getCurrentVersion(), 'dev-') === 0) {
                continue;
            }

            if ($version->isGreaterThan()) {
                return false;
            }
        }

        return true;
    }
}
