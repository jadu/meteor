<?php

namespace Meteor\Patch\Task;

class UpdateMigrationVersionFiles
{
    /**
     * @var string
     */
    public $backupDir;

    /**
     * @var string
     */
    public $patchDir;

    /**
     * @var string
     */
    public $installDir;

    /**
     * @param string $backupDir
     * @param string $patchDir
     * @param string $installDir
     */
    public function __construct($backupDir, $patchDir, $installDir)
    {
        $this->backupDir = $backupDir;
        $this->patchDir = $patchDir;
        $this->installDir = $installDir;
    }
}
