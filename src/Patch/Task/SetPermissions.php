<?php

namespace Meteor\Patch\Task;

class SetPermissions
{
    /**
     * @var string
     */
    public $sourceDir;

    /**
     * @var string
     */
    public $targetDir;

    /**
     * @param string $sourceDir
     * @param string $targetDir
     */
    public function __construct($sourceDir, $targetDir)
    {
        $this->sourceDir = $sourceDir;
        $this->targetDir = $targetDir;
    }
}
