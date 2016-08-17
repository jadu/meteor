<?php

namespace Meteor\Patch\Task;

class UpdateMigrationVersionFiles
{
    /**
     * @var string
     */
    public $patchDir;

    /**
     * @var string
     */
    public $installDir;

    /**
     * @param string $patchDir
     * @param string $installDir
     */
    public function __construct($patchDir, $installDir)
    {
        $this->patchDir = $patchDir;
        $this->installDir = $installDir;
    }
}
