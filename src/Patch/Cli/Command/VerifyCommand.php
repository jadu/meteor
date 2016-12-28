<?php

namespace Meteor\Patch\Cli\Command;

use Meteor\IO\IOInterface;
use Meteor\Patch\Manifest\ManifestChecker;
use Meteor\Platform\PlatformInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VerifyCommand extends AbstractPatchCommand
{
    /**
     * @var ManifestChecker
     */
    private $manifestChecker;

    /**
     * @param string $name
     * @param array $config
     * @param IOInterface $io
     * @param PlatformInterface $platform
     * @param ManifestChecker $manifestChecker
     */
    public function __construct($name, array $config, IOInterface $io, PlatformInterface $platform, ManifestChecker $manifestChecker)
    {
        parent::__construct($name, $config, $io, $platform);

        $this->manifestChecker = $manifestChecker;
    }

    protected function configure()
    {
        $this->setName('patch:verify');
        $this->setDescription('Verifies the package contents against the manifest.');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->manifestChecker->check($this->getWorkingDir());

        if ($result) {
            $this->io->success('The package was verified against the manifest.');
        } else {
            $this->io->error('The package could not be verified.');
        }
    }
}
