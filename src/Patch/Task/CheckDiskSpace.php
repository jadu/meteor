<?php

namespace Meteor\Patch\Task;

class CheckDiskSpace
{
    /**
     * @var string
     */
    public $installDir;

    /**
     * @var string
     */
    public $backupsDir;

    /**
     * @param string $installDir
     * @param string $backupsDir
     */
    public function __construct($installDir, $backupsDir)
    {
        $this->installDir = $installDir;
        $this->backupsDir = $backupsDir;
    }
}
