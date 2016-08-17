<?php

namespace Meteor\Package\Composer;

class ComposerProblem
{
    const REASON_MISSING = 0;
    const REASON_CONSTRAINT = 1;

    /**
     * @var ComposerRequirement
     */
    private $requirement;

    /**
     * @var int
     */
    private $reason;

    /**
     * @var string
     */
    private $installedVersion;

    /**
     * @param ComposerRequirement $requirement
     * @param int $reason
     * @param string $installedVersion
     */
    public function __construct(ComposerRequirement $requirement, $reason, $installedVersion = null)
    {
        $this->requirement = $requirement;
        $this->reason = $reason;
        $this->installedVersion = $installedVersion;
    }

    /**
     * @return bool
     */
    public function isMissing()
    {
        return $this->reason === self::REASON_MISSING;
    }

    /**
     * @return bool
     */
    public function isConstraintMismatch()
    {
        return $this->reason === self::REASON_CONSTRAINT;
    }

    public function __toString()
    {
        return sprintf('%s %s', $this->requirement, $this->isMissing() ? 'is missing' : sprintf('is required but version "%s" is installed', $this->installedVersion));
    }
}
