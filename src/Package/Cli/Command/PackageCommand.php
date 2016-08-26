<?php

namespace Meteor\Package\Cli\Command;

use InvalidArgumentException;
use Meteor\Cli\Command\AbstractCommand;
use Meteor\IO\IOInterface;
use Meteor\Package\PackageCreator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PackageCommand extends AbstractCommand
{
    /**
     * @var PackageCreator
     */
    private $packageCreator;

    /**
     * @param string $name
     * @param array $config
     * @param IOInterface $io
     * @param PackageCreator $packageCreator
     */
    public function __construct($name, array $config, IOInterface $io, PackageCreator $packageCreator)
    {
        parent::__construct($name, $config, $io);

        $this->packageCreator = $packageCreator;
    }

    protected function configure()
    {
        $this->setName('package');
        $this->setDescription('Creates a new package from the working directory.');
        $this->addOption('output-dir', 'o', InputOption::VALUE_REQUIRED, 'The directory to put the ZIP archive in.', 'output');
        $this->addOption('filename', 'f', InputOption::VALUE_REQUIRED, 'The name given to the package ZIP archive.');
        $this->addOption('combine', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'One or more packages to combine with this package.');
        $this->addOption('skip-combine', null, InputOption::VALUE_NONE, 'Do not combine packages automatically based on the config.');
        $this->addOption('phar', null, InputOption::VALUE_REQUIRED, 'The path to a Phar archive to bundle with the package.');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $created = $this->packageCreator->create(
            $this->getWorkingDir(),
            $this->getOutputDir(),
            $this->io->getOption('filename'),
            $this->getConfiguration(),
            $this->io->getOption('combine'),
            $this->io->getOption('skip-combine'),
            $this->io->getOption('phar')
        );

        if (!$created) {
            return 1;
        }
    }

    /**
     * @return string
     */
    private function getOutputDir()
    {
        $outputDir = rtrim($this->io->getOption('output-dir'), '/');
        if (trim($outputDir) === '') {
            throw new InvalidArgumentException('Missing the required --output-dir option.');
        }

        return $outputDir;
    }
}
