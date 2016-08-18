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
     * @param PlatformInterface $platform
     * @param PermissionLoader $permissionLoader
     * @param IOInterface $io
     */
    public function __construct(
        PlatformInterface $platform,
        PermissionLoader $permissionLoader,
        IOInterface $io
    ) {
        $this->platform = $platform;
        $this->permissionLoader = $permissionLoader;
        $this->io = $io;
    }

    /**
     * @param array $files
     * @param string $targetDir
     */
    public function setDefaultPermissions(array $files, $targetDir)
    {
        $targetDir = rtrim($targetDir, '/');

        $permissionErrors = array();
        foreach ($files as $file) {
            try {
                $this->platform->setDefaultPermission($targetDir, $file);
            } catch (Exception $exception) {
                $permissionErrors[] = $targetDir.'/'.$file;
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

        $permissionErrors = array();
        $permissions = $this->permissionLoader->load($targetDir);
        if (empty($permissions)) {
            return;
        }

        $this->io->progressStart(count($permissions));

        foreach ($permissions as $permission) {
            $this->io->debug(sprintf('%s <info>%s</>', $permission->getPattern(), $permission->getModeString()));

            $files = $this->resolvePattern($baseDir, $targetDir, $permission->getPattern());
            foreach ($files as $file) {
                try {
                    $this->platform->setPermission($file, $permission);
                } catch (Exception $exception) {
                    $permissionErrors[] = sprintf('%s (%s)', $file, $permission->getModeString());
                }
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

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
            $targetPath = $targetDir.'/'.$pattern;
            if (!file_exists($targetPath)) {
                // File does not exist
                return array();
            }

            return array($targetPath);
        }

        $basePath = $baseDir.'/'.$pattern;
        $baseDirname = dirname($basePath);
        if (!is_dir($baseDirname)) {
            // Directory does not exist
            return array();
        }

        $paths = array();
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
                $filePath = $targetDir.preg_replace('/'.preg_quote($baseDir, '/').'/', '', $filePath);
            }

            $paths[] = $filePath;
        }

        return $paths;
    }
}
