<?php

namespace Meteor\Permissions\Cli\Command;

use Meteor\IO\IOInterface;
use Meteor\Patch\Cli\Command\AbstractPatchCommand;
use Meteor\Permissions\PermissionSetter;
use Meteor\Platform\PlatformInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetPermissionsCommand extends AbstractPatchCommand
{
    /**
     * @var PermissionSetter
     */
    private $permissionSetter;

    /**
     * @param string $name
     * @param array $config
     * @param IOInterface $io
     * @param PlatformInterface $platform
     * @param PermissionSetter $permissionSetter
     */
    public function __construct($name, array $config, IOInterface $io, PlatformInterface $platform, PermissionSetter $permissionSetter)
    {
        parent::__construct($name, $config, $io, $platform);

        $this->permissionSetter = $permissionSetter;
    }

    protected function configure()
    {
        $this->setName('permissions:reset');
        $this->setDescription('Resets permissions of files in the install.');

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

        // NB: Find files and set permission in the same directory, which in this case should be the install directory
        $this->permissionSetter->setPermissions($installDir, $installDir);
    }
}
