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
     * @var bool
     */
    public $ignoreUnavailableMigrations;

    /**
     * @param string $backupDir
     * @param string $workingDir
     * @param string $installDir
     * @param string $type
     * @param bool $ignoreUnavailableMigrations
     */
    public function __construct($backupDir, $workingDir, $installDir, $type, $ignoreUnavailableMigrations)
    {
        $this->backupDir = $backupDir;
        $this->workingDir = $workingDir;
        $this->installDir = $installDir;
        $this->type = $type;
        $this->ignoreUnavailableMigrations = $ignoreUnavailableMigrations;
    }
}
