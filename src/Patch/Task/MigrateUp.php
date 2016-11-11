<?php

namespace Meteor\Patch\Task;

class MigrateUp
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
     * @var string
     */
    public $type;

    /**
     * @var bool
     */
    public $ignoreUnavailableMigrations;

    /**
     * @param string $workingDir
     * @param string $installDir
     * @param string $type
     * @param bool $ignoreUnavailableMigrations
     */
    public function __construct($workingDir, $installDir, $type, $ignoreUnavailableMigrations)
    {
        $this->workingDir = $workingDir;
        $this->installDir = $installDir;
        $this->type = $type;
        $this->ignoreUnavailableMigrations = $ignoreUnavailableMigrations;
    }
}
