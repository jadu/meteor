<?php

namespace Meteor\Package\Composer;

class ComposerRequirement
{
    /**
     * @var string
     */
    private $packageName;

    /**
     * @var string
     */
    private $versionConstraint;

    /**
     * @param string $packageName
     * @param string $versionConstraint
     */
    public function __construct($packageName, $versionConstraint)
    {
        $this->packageName = $packageName;
        $this->versionConstraint = $versionConstraint;
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
    public function getVersionConstraint()
    {
        return $this->versionConstraint;
    }

    public function __toString()
    {
        return sprintf('%s (%s)', $this->getPackageName(), $this->getVersionConstraint());
    }
}
