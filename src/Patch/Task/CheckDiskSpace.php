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
    public $backupDir;

    /**
     * @param string $installDir
     * @param string $backupDir
     */
    public function __construct($installDir, $backupDir)
    {
        $this->installDir = $installDir;
        $this->backupDir = $backupDir;
    }
}
