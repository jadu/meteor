<?php

namespace Meteor\Patch\Cli\Command;

use Meteor\IO\IOInterface;
use Meteor\Patch\Lock\Locker;
use Meteor\Platform\PlatformInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearLockCommand extends AbstractPatchCommand
{
    /**
     * @var Locker
     */
    private $locker;

    /**
     * @param string $name
     * @param array $config
     * @param IOInterface $io
     * @param PlatformInterface $platform
     * @param Locker $locker
     */
    public function __construct($name, array $config, IOInterface $io, PlatformInterface $platform, Locker $locker)
    {
        parent::__construct($name, $config, $io, $platform);

        $this->locker = $locker;
    }

    protected function configure()
    {
        $this->setName('patch:clear-lock');
        $this->setDescription('Clears the lock file so a patch can be run again.');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $installDir = $this->getInstallDir();

        $result = $this->locker->unlock($installDir);

        if ($result) {
            $this->io->success('Cleared lock file.');
        } else {
            $this->io->note('The install was not locked.');
        }
    }
}
