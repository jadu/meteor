<?php

namespace Meteor\Platform\Windows;

use Meteor\Permissions\Permission;
use Meteor\Platform\PlatformInterface;
use Meteor\Process\ProcessRunner;

class WindowsPlatform implements PlatformInterface
{
    /**
     * @var ProcessRunner
     */
    protected $processRunner;

    /**
     * @param ProcessRunner $processRunner
     */
    public function __construct(ProcessRunner $processRunner)
    {
        $this->processRunner = $processRunner;
    }

    /**
     * {@inheritdoc}
     */
    public function setInstallDir($installDir)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultInstallDir()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultPermission($baseDir, $path)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setPermission($path, Permission $permission)
    {
        $user = 'IIS_IUSRS';

        $grant = $user . ':';
        if (is_dir($path)) {
            $grant .= '(OI)(CI)';
        }
        $grant .= $this->getModeString($permission);

        $command = ['icacls', $path, '/remove:g', $user, '/grant', $grant];

        if (is_dir($path) && $permission->isRecursive()) {
            $command[] = '/t';
        }

        $command[] = '/Q';


        $this->processRunner->run($command);
    }

    /**
     * Returns the icalcs mode string for the permission.
     *
     * @param Permission $permission
     *
     * @return string
     */
    protected function getModeString(Permission $permission)
    {
        $modes = '';
        if ($permission->canRead()) {
            $modes .= 'R';
        }

        if ($permission->canExecute()) {
            if (!$permission->canRead()) {
                // Require read permission to execute
                $modes .= 'R';
            }
            $modes .= 'X';
        }

        if ($permission->canWrite()) {
            $modes .= 'WM';
        }

        return $modes;
    }
}
