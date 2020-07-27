<?php

namespace Meteor\Process;

class PHPMemoryLimitSetter
{
    const RE_PHP_SCRIPT = '/^\s*?(php)/';
    const RE_MEMORY_LIMIT = '/([-]{1,2}(?:define|d)\s*(memory_limit))/';

    /**
     * @param string $command
     *
     * @return bool
     */
    public static function isPHPScript($command)
    {
        return preg_match(self::RE_PHP_SCRIPT, $command);
    }

    /**
     * @param string $command
     *
     * @return bool
     */
    public static function hasMemoryLimit($command)
    {
        return preg_match(self::RE_MEMORY_LIMIT, $command);
    }

    /**
     * @param string $command
     *
     * @return string
     */
    public static function setMemoryLimit($command)
    {
        return preg_replace(
            self::RE_PHP_SCRIPT,
            sprintf('php --define memory_limit=%s', ini_get('memory_limit')),
            $command
        );
    }
}
