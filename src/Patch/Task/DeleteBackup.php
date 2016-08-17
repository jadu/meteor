<?php

namespace Meteor\Patch\Task;

class DeleteBackup
{
    /**
     * @var sting
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
