<?php

namespace Meteor\Patch\Task;

use Meteor\Configuration\ConfigurationLoader;
use Meteor\Filesystem\Filesystem;
use Meteor\IO\IOInterface;
use Meteor\Package\PackageConstants;

class BackupFilesHandler
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ConfigurationLoader
     */
    private $configurationLoader;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param Filesystem $filesystem
     * @param ConfigurationLoader $configurationLoader
     * @param IOInterface $io
     */
    public function __construct(Filesystem $filesystem, ConfigurationLoader $configurationLoader, IOInterface $io)
    {
        $this->filesystem = $filesystem;
        $this->configurationLoader = $configurationLoader;
        $this->io = $io;
    }

    /**
     * @param BackupFiles $task
     * @param array $config
     */
    public function handle(BackupFiles $task, array $config)
    {
        $this->io->text(sprintf('Creating backup in <info>%s</info>', $task->backupDir));

        $this->filesystem->ensureDirectoryExists($task->backupDir);

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

        array_walk($replaceDirectories, function(&$directory) { $directory = ltrim($directory, '/\\'); });

        $excludeFilters = [];

        if (!empty($replaceDirectories)) {
            $excludeFilters[] = '**';

            foreach ($replaceDirectories as $directory) {
                $excludeFilters[] = '!' . DIRECTORY_SEPARATOR . $directory;
            }
        }

        // Copy the files from the install that exist in the patch to the backup
        $this->io->text('Copying files from the install to the backup:');
        $files = $this->filesystem->findFiles($task->patchDir . '/' . PackageConstants::PATCH_DIR, $excludeFilters);

        // Backup everything in the install that is marked as a replace directory
        foreach ($replaceDirectories as $directory) {
            $path = $task->installDir . DIRECTORY_SEPARATOR . $directory;

            $this->io->debug(sprintf("Adding replace directory %s to backup files", $path));
            $replaceFiles = $this->filesystem->findFiles($path);
            array_walk($replaceFiles, function(&$path) use ($directory) { $path = $directory . DIRECTORY_SEPARATOR . $path; });

            $files = array_merge($files, [$directory], $replaceFiles);
        }

        $this->filesystem->copyFiles($files, $task->installDir, $task->backupDir . DIRECTORY_SEPARATOR . PackageConstants::PATCH_DIR);

        // Copy the meteor.json into the backup
        $configPath = $this->configurationLoader->resolve($task->patchDir);
        $this->filesystem->copy($configPath, $task->backupDir . '/meteor.json.package', true);
    }
}
