<?php

namespace Meteor\Package\Combined\Exception;

use Exception;
use Meteor\Package\Combined\CombinedPackageProblem;

class CombinedPackageDependenciesException extends Exception
{
    /**
     * @var CombinedPackageProblem[]
     */
    private $problems = array();

    /**
     * @return CombinedPackageProblem[]
     */
    public function getProblems()
    {
        return $this->problems;
    }

    /**
     * @param CombinedPackageProblem[] $problems
     */
    public function setProblems(array $problems)
    {
        $this->problems = $problems;
    }

    /**
     * @param string $lockpath
     * @param CombinedPackageProblem[] $problems
     *
     * @return self
     */
    public static function withProblems(array $problems)
    {
        $exception = new self('The combined packages did not meet the requirements');
        $exception->setProblems($problems);

        return $exception;
    }
}
