<?php

namespace Meteor\Platform;

use Meteor\Permissions\Permission;

interface PlatformInterface
{
    /**
     * @param string
     */
    public function setInstallDir($installDir);

    /**
     * @return string
     */
    public function getDefaultInstallDir();

    /**
     * @param string $baseDir
     * @param string $path
     */
    public function setDefaultPermission($baseDir, $path);

    /**
     * @param string $path
     * @param Permission $permission
     */
    public function setPermission($path, Permission $permission);
}
