<?php

namespace Meteor\Patch\Task;

class LimitBackups
{
    /**
     * @var string
     */
    public $installDir;

    /**
     * @var string
     */
    public $backups;

    /**
     * @param        $installDir
     * @param        $backups
     */
    public function __construct($installDir, $backups)
    {
        $this->installDir = $installDir;
        $this->backups = $backups;
    }
}
