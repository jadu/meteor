<?php

namespace Meteor\Permissions\Cli\Command;

use Meteor\Filesystem\Filesystem;
use Meteor\IO\IOInterface;
use Meteor\Patch\Cli\Command\AbstractPatchCommand;
use Meteor\Permissions\PermissionSetter;
use Meteor\Platform\PlatformInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResetPermissionsCommand extends AbstractPatchCommand
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var PermissionSetter
     */
    private $permissionSetter;

    /**
     * @param string $name
     * @param array $config
     * @param IOInterface $io
     * @param PlatformInterface $platform
     * @param Filesystem $filesystem
     * @param PermissionSetter $permissionSetter
     */
    public function __construct($name, array $config, IOInterface $io, PlatformInterface $platform, Filesystem $filesystem, PermissionSetter $permissionSetter)
    {
        parent::__construct($name, $config, $io, $platform);

        $this->filesystem = $filesystem;
        $this->permissionSetter = $permissionSetter;
    }

    protected function configure()
    {
        $this->setName('permissions:reset');
        $this->setDescription('Resets permissions of files in the install.');
        $this->addOption('--default', null, InputOption::VALUE_NONE, 'Set default permissions on all files');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $installDir = $this->getInstallDir();
        $this->platform->setInstallDir($installDir);

        if ($this->io->getOption('default')) {
            // Set the default permissions for all files within the package
            $files = $this->filesystem->findFiles($this->getWorkingDir());
            $this->permissionSetter->setDefaultPermissions($files, $installDir);
        }

        // NB: Find files and set permission in the same directory, which in this case should be the install directory
        $this->permissionSetter->setPermissions($installDir, $installDir);
    }
}
