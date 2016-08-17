<?php

namespace Meteor\Platform\Unix;

use Meteor\Filesystem\Filesystem;
use Meteor\Permissions\Permission;
use Meteor\Platform\PlatformInterface;
use RuntimeException;

class UnixPlatform implements PlatformInterface
{
    /**
     * @var InstallConfig
     */
    private $installConfig;

    /**
     * @var InstallConfigLoader
     */
    private $installConfigLoader;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param filesystem $installConfigLoader
     * @param Filesystem $filesystem
     */
    public function __construct(InstallConfigLoader $installConfigLoader, Filesystem $filesystem)
    {
        $this->installConfigLoader = $installConfigLoader;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function setInstallDir($installDir)
    {
        $this->installConfig = $this->installConfigLoader->load($installDir);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultInstallDir()
    {
        $installDir = '/var/www/jadu';
        if (is_dir($installDir)) {
            return $installDir;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultPermission($baseDir, $path)
    {
        $this->assertHasInstallConfig();

        // When the file is not in the `jadu/` path then use the web group
        $owningGroup = strpos($path, 'jadu/') === 0 ? $this->installConfig->getGroup() : $this->installConfig->getWebGroup();

        $baseDir = rtrim($baseDir, '/');
        $path = $baseDir.'/'.$path;

        $this->filesystem->chgrp($path, $owningGroup);
        $this->filesystem->chown($path, $this->installConfig->getUser());
        $this->filesystem->chmod($path, $this->getDefaultMode($path));
    }

    /**
     * {@inheritdoc}
     */
    public function setPermission($path, Permission $permission)
    {
        $this->assertHasInstallConfig();

        $recursive = is_dir($path) && $permission->isRecursive();

        $this->filesystem->chgrp($path, $this->installConfig->getWebGroup(), $recursive);

        // NB: The default mode is used as the umask
        $this->filesystem->chmod($path, $this->getMode($path, $permission), 0000, $recursive);
    }

    /**
     * @param string $path
     *
     * @return octal
     */
    private function getDefaultMode($path)
    {
        return is_dir($path) ? 0750 : 0640;
    }

    /**
     * @param string $path
     * @param FilePermission $permission
     *
     * @return string
     */
    private function getMode($path, Permission $permission)
    {
        $groupMode = 0;
        if ($permission->canRead()) {
            $groupMode += 4;
        }
        if ($permission->canWrite()) {
            $groupMode += 2;
        }
        if ($permission->canExecute()) {
            $groupMode += 1;
        }

        return octdec('00'.$groupMode.'0') | (is_dir($path) ? 0700 : 0600);
    }

    /**
     * @throws RuntimeException
     */
    private function assertHasInstallConfig()
    {
        if ($this->installConfig === null) {
            throw new RuntimeException('Install config not initialized');
        }
    }
}
