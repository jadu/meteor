<?php

namespace Meteor\Patch\Task;

class CheckWritePermission
{
    /**
     * @var string
     */
    public $targetDir;

    /**
     * @param string $targetDir
     */
    public function __construct($targetDir)
    {
        $this->targetDir = $targetDir;
    }
}
