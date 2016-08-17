<?php

namespace Meteor\Patch\Task;

class BackupFiles
{
    /**
     * @var string
     */
    public $timestamp;

    /**
     * @var string
     */
    public $patchDir;

    /**
     * @var string
     */
    public $installDir;

    /**
     * @param string $timestamp
     * @param string $patchDir
     * @param string $installDir
     */
    public function __construct($timestamp, $patchDir, $installDir)
    {
        $this->timestamp = $timestamp;
        $this->patchDir = $patchDir;
        $this->installDir = $installDir;
    }
}
