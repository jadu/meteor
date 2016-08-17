<?php

namespace Meteor\Patch\Task;

class DisplayVersionInfo
{
    /**
     * @var string
     */
    public $workingDir;

    /**
     * @var string
     */
    public $installDir;

    /**
     * @param string $workingDir
     * @param string $installDir
     */
    public function __construct($workingDir, $installDir)
    {
        $this->workingDir = $workingDir;
        $this->installDir = $installDir;
    }
}
