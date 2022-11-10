<?php

namespace Meteor\Migrations\Cli\Command;

use Meteor\IO\IOInterface;
use Meteor\Migrations\Outputter\StatusOutputter;
use Meteor\Patch\Cli\Command\AbstractPatchCommand;
use Meteor\Platform\PlatformInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends AbstractPatchCommand
{
    /**
     * @var StatusOutputter
     */
    private $statusOutputter;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $name
     * @param array $config
     * @param IOInterface $io
     * @param PlatformInterface $platform
     * @param StatusOutputter $statusOutputter
     */
    public function __construct($name, array $config, IOInterface $io, PlatformInterface $platform, StatusOutputter $statusOutputter, $type)
    {
        parent::__construct($name, $config, $io, $platform);

        $this->statusOutputter = $statusOutputter;
        $this->type = $type;
    }

    protected function configure()
    {
        $this->setDescription('Displays the status of the migrations.');
        $this->addArgument('package', InputArgument::OPTIONAL);
        $this->addOption('show-versions', null, InputOption::VALUE_NONE, 'This will display a list of all available migrations and their status');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getConfiguration();

        $packageName = $this->io->getArgument('package');
        $showVersions = $this->io->getOption('show-versions');

        if ($packageName === null) {
            if (empty($config)) {
                $this->io->error('There are no migrations configured');

                return Command::FAILURE;
            }

            foreach ($config as $packageName => $migrationConfig) {
                $this->io->title(sprintf('Migration status for <info>%s</>', $packageName));

                $this->statusOutputter->output(
                    $this->getWorkingDir(),
                    $this->getInstallDir(),
                    $migrationConfig,
                    $this->type,
                    $showVersions
                );
            }

            return Command::SUCCESS;
        }

        if (!isset($config[$packageName])) {
            $this->io->error(sprintf('Unable to find migrations for the package "%s"', $packageName));

            return Command::FAILURE;
        }

        $this->io->title(sprintf('Migration status for <info>%s</>', $packageName));

        $this->statusOutputter->output(
            $this->getWorkingDir(),
            $this->getInstallDir(),
            $config[$packageName],
            $this->type,
            $showVersions
        );

        return Command::SUCCESS;
    }
}
