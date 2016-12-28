<?php

namespace Meteor\Patch\Cli\Command;

use Meteor\IO\IOInterface;
use Meteor\Package\PackageConstants;
use Meteor\Patch\Task\DisplayVersionInfo;
use Meteor\Patch\Task\TaskBusInterface;
use Meteor\Platform\PlatformInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VersionInfoCommand extends AbstractPatchCommand
{
    /**
     * @var TaskBusInterface
     */
    private $taskBus;

    /**
     * @param string $name
     * @param array $config
     * @param IOInterface $io
     * @param PlatformInterface $platform
     * @param TaskBusInterface $taskBus
     */
    public function __construct($name, array $config, IOInterface $io, PlatformInterface $platform, TaskBusInterface $taskBus)
    {
        parent::__construct($name, $config, $io, $platform);

        $this->taskBus = $taskBus;
    }

    protected function configure()
    {
        $this->setName('patch:version-info');
        $this->setDescription('Displays the version information from the package.');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $task = new DisplayVersionInfo(
            $this->getWorkingDir() . '/' . PackageConstants::PATCH_DIR,
            $this->getInstallDir()
        );

        $this->taskBus->run($task, $this->getConfiguration());
    }
}
