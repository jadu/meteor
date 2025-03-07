<?php

namespace Meteor\Package\Combined;

class CombinedPackageProblem
{
    public const REASON_MISSING = 0;
    public const REASON_VERSION = 1;

    /**
     * @var CombinedPackageRequirement
     */
    private $requirement;

    /**
     * @var int
     */
    private $reason;

    /**
     * @var string
     */
    private $foundVersion;

    /**
     * @param CombinedPackageRequirement $requirement
     * @param int $reason
     * @param string $foundVersion
     */
    public function __construct(CombinedPackageRequirement $requirement, $reason, $foundVersion = null)
    {
        $this->requirement = $requirement;
        $this->reason = $reason;
        $this->foundVersion = $foundVersion;
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
    public function isVersionMismatch()
    {
        return $this->reason === self::REASON_VERSION;
    }

    public function __toString()
    {
        return sprintf('%s %s', $this->requirement, $this->isMissing() ? 'is missing' : sprintf('is required but version "%s" was found', $this->foundVersion));
    }
}
