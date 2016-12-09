<?php

namespace Meteor\Patch\Task;

class DeleteBackup
{
    /**
     * @var string
     */
    public $backupDir;

    /**
     * @param string $backupDir
     */
    public function __construct($backupDir)
    {
        $this->backupDir = $backupDir;
    }
}
