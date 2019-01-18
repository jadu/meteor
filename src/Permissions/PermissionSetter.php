<?php

namespace Meteor\Permissions;

use Exception;
use Meteor\IO\IOInterface;
use Meteor\Platform\PlatformInterface;
use Symfony\Component\Finder\Finder;

class PermissionSetter
{
    /**
     * @var PlatformInterface
     */
    private $platform;

    /**
     * @var PermissionLoader
     */
    private $permissionLoader;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var array
     */
    private $postApplyPermissions = [
        'var/cache/*' => 'rwxR',
        'logs/*' => 'rwR'
    ];

    /**
     * @param PlatformInterface $platform
     * @param PermissionLoader  $permissionLoader
     * @param IOInterface       $io
     */
    public function __construct(
        PlatformInterface $platform, PermissionLoader $permissionLoader, IOInterface $io
    ) {
        $this->platform = $platform;
        $this->permissionLoader = $permissionLoader;
        $this->io = $io;
    }

    /**
     * @param array  $files
     * @param string $targetDir
     */
    public function setDefaultPermissions(array $files, $targetDir)
    {
        $targetDir = rtrim($targetDir, '/');

        $permissionErrors = [];
        foreach ($files as $file) {
            try {
                $this->platform->setDefaultPermission($targetDir, $file);
            } catch (Exception $exception) {
                $permissionErrors[] = $targetDir . '/' . $file;
            }
        }

        if (!empty($permissionErrors)) {
            $this->io->warning('Unable to set default permissions correctly for some files. They should be set manually or by running `meteor permissions:reset` with the correct permission.');
            $this->io->listing($permissionErrors);
        }
    }

    /**
     * @param string $baseDir
     * @param string $targetDir
     */
    public function setPermissions($baseDir, $targetDir)
    {
        $baseDir = rtrim($baseDir, '/');
        $targetDir = rtrim($targetDir, '/');

        $this->io->text(sprintf('Setting file permissions in <info>%s</>', $targetDir));

        $permissionErrors = [];
        $permissions = $this->permissionLoader->load($targetDir);
        if (empty($permissions)) {
            return;
        }

        foreach ($permissions as $permission) {
            $files = $this->resolvePattern($baseDir, $targetDir, $permission->getPattern());

            if (!empty($files)) {
                $this->io->text(sprintf('%s <info>%s</>', $permission->getPattern(), $permission->getModeString()));
                $this->io->progressStart(count($files));

                foreach ($files as $file) {
                    try {
                        $this->platform->setPermission($file, $permission);
                    } catch (Exception $exception) {
                        $permissionErrors[] = sprintf('%s (%s)', $file, $permission->getModeString());
                    }

                    $this->io->progressAdvance();
                }

                $this->io->progressFinish();
            }
        }

        if (!empty($permissionErrors)) {
            $this->io->warning('Unable to set permissions correctly for some files. They should be set manually or by running `meteor permissions:reset` with the correct permission.');
            $this->io->listing($permissionErrors);
        }

        $this->io->newLine();
    }


    /**
     * @param string $targetDir
     */
    public function setPostApplyPermissions($targetDir)
    {
        $permissions = $this->permissionLoader->loadFromArray($this->postApplyPermissions);
        $this->io->text(sprintf('Setting post apply file permissions in <info>%s</>', $targetDir));

        foreach ($permissions as $permission) {
            $files = $this->resolvePattern($targetDir, $targetDir, $permission->getPattern());
            if (!empty($files)) {
                $this->io->text(sprintf('%s <info>%s</>', $permission->getPattern(), $permission->getModeString()));
                $this->io->progressStart(count($files));

                foreach ($files as $file) {
                    try {
                        $this->platform->setPermission($file, $permission);
                    } catch (Exception $exception) {
                        $permissionErrors[] = sprintf('%s (%s)', $file, $permission->getModeString());
                    }

                    $this->io->progressAdvance();
                }

                $this->io->progressFinish();
            }
        }

        if (!empty($permissionErrors)) {
            $this->io->warning('Unable to set permissions correctly for some files. They should be set manually or by running `meteor permissions:reset` with the correct permission.');
            $this->io->listing($permissionErrors);
        }

        $this->io->newLine();
    }

    /**
     * @param string $baseDir
     * @param string $targetDir
     * @param string $path
     *
     * @return array
     */
    private function resolvePattern($baseDir, $targetDir, $pattern)
    {
        if (strpos($pattern, '*') === false) {
            $basePath = $baseDir . '/' . $pattern;
            $targetPath = $targetDir . '/' . $pattern;

            if (!file_exists($basePath) || !file_exists($targetPath)) {
                // File does not exist
                return [];
            }

            return [$targetPath];
        }

        $basePath = $baseDir . '/' . $pattern;
        $baseDirname = dirname($basePath);
        if (!is_dir($baseDirname)) {
            // Directory does not exist
            return [];
        }

        $paths = [];
        $finder = new Finder();
        $finder->in($baseDirname);
        $finder->ignoreVCS(true);
        $finder->ignoreDotFiles(false);

        $basename = basename($pattern);
        if ($basename !== '*') {
            // Partial match on file name, .e.g public_html/.htaccess*
            $finder->name($basename);
        }

        foreach ($finder->depth('== 0') as $fileInfo) {
            $filePath = $fileInfo->getPathname();
            if ($baseDir !== $targetDir) {
                $filePath = $targetDir . preg_replace('/' . preg_quote($baseDir, '/') . '/', '', $filePath);
            }

            $paths[] = $filePath;
        }

        return $paths;
    }

    public function getPostApplyPermissions()
    {
        return $this->postApplyPermissions;
    }
}
