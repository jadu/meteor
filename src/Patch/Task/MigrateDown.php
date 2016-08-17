<?php

namespace Meteor\Patch\Task;

class MigrateDown
{
    /**
     * @var string
     */
    public $backupDir;

    /**
     * @var string
     */
    public $workingDir;

    /**
     * @var string
     */
    public $installDir;

    /**
     * @var string
     */
    public $type;

    /**
     * @param string $backupDir
     * @param string $workingDir
     * @param string $installDir
     * @param string $type
     */
    public function __construct($backupDir, $workingDir, $installDir, $type)
    {
        $this->backupDir = $backupDir;
        $this->workingDir = $workingDir;
        $this->installDir = $installDir;
        $this->type = $type;
    }
}
