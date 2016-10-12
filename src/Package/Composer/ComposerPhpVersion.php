<?php

namespace Meteor\Package\Composer;

class ComposerPhpVersion
{
    /**
     * @var string
     */
    private $versionConstraint;

    /**
     * @param string $versionConstraint
     */
    public function __construct($versionConstraint)
    {
        $this->versionConstraint = $versionConstraint;
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
        return sprintf('PHP (%s)', $this->getVersionConstraint());
    }
}
