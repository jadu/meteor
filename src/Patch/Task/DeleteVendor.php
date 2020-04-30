<?php

namespace Meteor\Patch\Task;

class DeleteVendor
{
    /**
     * @var string
     */
    public $installDir;

    /**
     * @param string $installDir
     */
    public function __construct($installDir)
    {
        $this->installDir = $installDir;
    }

    public function getVendorFolder()
    {
        return $this->installDir . DIRECTORY_SEPARATOR .  'vendor';
    }
}
