<?php

namespace Meteor\Patch\Task;

class LimitBackups
{
    /**
     * @var string
     */
    public $backupsDir;

    /**
     * @var string
     */
    public $installDir;

    /**
     * @var string
     */
    public $backups;

    /**
     * @param string $backupsDir
     * @param string $installDir
     * @param string $backups
     */
    public function __construct($backupsDir, $installDir, $backups)
    {
        $this->backupsDir = $backupsDir;
        $this->installDir = $installDir;
        $this->backups = $backups;
    }
}
