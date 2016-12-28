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
        $this->io->text(sprintf('Creating backup in <info>%s</>', $task->backupDir));

        $this->filesystem->ensureDirectoryExists($task->backupDir);

        // Copy the files from the install that exist in the patch to the backup
        $this->io->text('Copying files from the install to the backup:');
        $files = $this->filesystem->findFiles($task->patchDir . '/' . PackageConstants::PATCH_DIR);
        $this->filesystem->copyFiles($files, $task->installDir, $task->backupDir . '/' . PackageConstants::PATCH_DIR);

        // Copy the meteor.json into the backup
        $configPath = $this->configurationLoader->resolve($task->patchDir);
        $this->filesystem->copy($configPath, $task->backupDir . '/meteor.json.package', true);
    }
}
