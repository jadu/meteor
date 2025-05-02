<?php

namespace Meteor\Patch\Task;

class CheckDiskSpace
{
    /**
     * @var string
     */
    public $installDir;

    /**
     * @var string
     */
    public $backupsDir;

    /**
     * @var string
     */
    public $patchFilesDir;

    /**
     * @param string $installDir
     * @param string $backupsDir
     */
    public function __construct($installDir, $backupsDir, $patchFilesDir)
    {
        $this->installDir = $installDir;
        $this->backupsDir = $backupsDir;
        $this->patchFilesDir = $patchFilesDir;
    }
}
