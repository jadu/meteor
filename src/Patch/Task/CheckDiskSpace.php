<?php

namespace Meteor\Patch\Task;

class CheckDiskSpace
{
    /**
     * @var string
     */
    public $installDir;

    /**
     * @param string $installDir
     */
    public function __construct($installDir)
    {
        $this->installDir = $installDir;
    }
}
