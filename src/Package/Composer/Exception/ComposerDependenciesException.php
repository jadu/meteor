<?php

namespace Meteor\Package\Composer\Exception;

use Exception;
use Meteor\Package\Composer\ComposerProblem;

class ComposerDependenciesException extends Exception
{
    /**
     * @var ComposerProblem[]
     */
    private $problems = [];

    /**
     * @return ComposerProblem[]
     */
    public function getProblems()
    {
        return $this->problems;
    }

    /**
     * @param ComposerProblem[] $problems
     */
    public function setProblems(array $problems)
    {
        $this->problems = $problems;
    }

    /**
     * @param string $lockpath
     *
     * @return self
     */
    public static function forInvalidJsonFile($lockPath)
    {
        return new self(sprintf('The composer.json file "%s" could not be parsed', $lockPath));
    }

    /**
     * @param string $lockpath
     *
     * @return self
     */
    public static function forMissingLockFile($lockPath)
    {
        return new self(sprintf('The composer.lock file "%s" does not exist', $lockPath));
    }

    /**
     * @param string $lockpath
     *
     * @return self
     */
    public static function forInvalidLockFile($lockPath)
    {
        return new self(sprintf('The composer.lock file "%s" could not be parsed', $lockPath));
    }

    /**
     * @param string $lockpath
     * @param ComposerProblem[] $problems
     *
     * @return self
     */
    public static function withProblems($lockPath, array $problems)
    {
        $exception = new self(sprintf('The composer.lock file "%s" did not meet the requirements', $lockPath));
        $exception->setProblems($problems);

        return $exception;
    }
}
