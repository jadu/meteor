<?php

namespace Meteor\Patch\Task;

use Meteor\Filesystem\Filesystem;
use Meteor\IO\IOInterface;
use Meteor\Permissions\PermissionSetter;

class CopyFilesHandler
{
    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var PermissionSetter
     */
    private $permissionSetter;

    /**
     * @param IOInterface $io
     * @param Filesystem $filesystem
     * @param PermissionSetter $permissionSetter
     */
    public function __construct(IOInterface $io, Filesystem $filesystem, PermissionSetter $permissionSetter)
    {
        $this->io = $io;
        $this->filesystem = $filesystem;
        $this->permissionSetter = $permissionSetter;
    }

    /**
     * @param CopyFiles $task
     * @param array $config
     */
    public function handle(CopyFiles $task, array $config)
    {
        $this->io->text(sprintf('Copying files into the install <info>%s</>', $task->targetDir));

        $replaceDirectories = $config['patch']['replace_directories'];

        if (isset($config['combined'])) {
            foreach ($config['combined'] as $package) {
                if (!isset($package['patch'])) {
                    continue;
                }

                $replaceDirectories = array_merge(
                    $replaceDirectories,
                    $package['patch']['replace_directories']
                );
            }
        }

        array_walk($replaceDirectories, function (&$directory) { $directory = ltrim($directory, '/\\'); });

        $excludeFilters = [];

        if (!empty($replaceDirectories)) {
            $excludeFilters[] = '**';

            foreach ($replaceDirectories as $directory) {
                $excludeFilters[] = '!' . DIRECTORY_SEPARATOR . $directory;
            }
        }

        $newFiles = $this->filesystem->findNewFiles($task->sourceDir, $task->targetDir, $excludeFilters);
        $this->filesystem->copyDirectory($task->sourceDir, $task->targetDir, $excludeFilters);

        foreach ($replaceDirectories as $directory) {
            $this->filesystem->replaceDirectory($task->sourceDir, $task->targetDir, $directory);
        }

        $this->permissionSetter->setDefaultPermissions($newFiles, $task->targetDir);
    }
}
