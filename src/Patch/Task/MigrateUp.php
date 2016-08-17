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
     * @param string $workingDir
     * @param string $installDir
     * @param string $type
     */
    public function __construct($workingDir, $installDir, $type)
    {
        $this->workingDir = $workingDir;
        $this->installDir = $installDir;
        $this->type = $type;
    }
}
