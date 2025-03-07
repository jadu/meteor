<?php

namespace Meteor\Patch\Task;

use InvalidArgumentException;

class CheckVersion
{
    public const GREATER_THAN_OR_EQUAL = '>=';
    public const LESS_THAN_OR_EQUAL = '<=';

    /**
     * @var string
     */
    public $workingDir;

    /**
     * @var string
     */
    public $installDir;

    /**
     * @var string
     */
    public $operator;

    /**
     * @param string $workingDir
     * @param string $installDir
     * @param string $operator
     */
    public function __construct($workingDir, $installDir, $operator)
    {
        $this->workingDir = $workingDir;
        $this->installDir = $installDir;

        if (!in_array($operator, [self::GREATER_THAN_OR_EQUAL, self::LESS_THAN_OR_EQUAL], true)) {
            throw new InvalidArgumentException('Invalid operator');
        }

        $this->operator = $operator;
    }
}
