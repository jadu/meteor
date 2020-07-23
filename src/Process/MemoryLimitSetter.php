<?php

namespace Meteor\Process;

/**
 * Class MemoryLimitSetter
 *
 * @author Jadu Ltd.
 */
class MemoryLimitSetter
{
    const RE_PHP_SCRIPT = '/^\s*?(php)/';
    const RE_MEMORY_LIMIT = '/([-]{1,2}(?:define|d)\s*(memory_limit))/';

    /**
     * @param string $command
     * @return boolean
     */
    public function isPHPScript($command)
    {
        return preg_match(self::RE_PHP_SCRIPT, $command);
    }

    /**
     * @param string $command
     * @return boolean
     */
    public function hasMemoryLimit($command)
    {
        return preg_match(self::RE_MEMORY_LIMIT, $command);
    }

    /**
     * @param string $command
     * @return string
     */
    public function setMemoryLimit($command)
    {
        return preg_replace(
            self::RE_PHP_SCRIPT,
            sprintf('php --define memory_limit=%d', ini_get('memory_limit')),
            $command
        );
    }
}
