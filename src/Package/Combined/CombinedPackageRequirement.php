<?php

namespace Meteor\Package\Combined;

class CombinedPackageRequirement
{
    /**
     * @var string
     */
    private $packageName;

    /**
     * @var string
     */
    private $version;

    /**
     * @param string $packageName
     * @param string $version
     */
    public function __construct($packageName, $version)
    {
        $this->packageName = $packageName;
        $this->version = $version;
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
    public function getVersion()
    {
        return $this->version;
    }

    public function __toString()
    {
        return sprintf('%s (%s)', $this->getPackageName(), $this->getVersion());
    }
}
