<?php

namespace Meteor\Migrations\Cli\Command;

use Meteor\IO\IOInterface;
use Meteor\Migrations\Version\VersionManager;
use Meteor\Platform\PlatformInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VersionCommand extends AbstractMigrationCommand
{
    /**
     * @var VersionManager
     */
    private $versionManager;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $name
     * @param array $config
     * @param IOInterface $io
     * @param PlatformInterface $platform
     * @param VersionManager $versionManager
     */
    public function __construct($name, array $config, IOInterface $io, PlatformInterface $platform, VersionManager $versionManager, $type)
    {
        parent::__construct($name, $config, $io, $platform);

        $this->versionManager = $versionManager;
        $this->type = $type;
    }

    protected function configure()
    {
        $help = <<<EOT
The <info>%command.name%</info> command allows you to manually add, delete or synchronize migration versions:

    <info>%command.full_name% jadu/cms YYYYMMDDHHMMSS --add</info>

If you want to delete a version you can use the <comment>--delete</comment> option:

    <info>%command.full_name% jadu/cms YYYYMMDDHHMMSS --delete</info>

If you want to synchronize by adding or deleting all migration versions available you can use the <comment>--all</comment> option:

    <info>%command.full_name% jadu/cms --add --all</info>
    <info>%command.full_name% jadu/cms --delete --all</info>
EOT;

        $this->setDescription('Manually add and delete migration versions from the version table.');
        $this->addArgument('package', InputArgument::OPTIONAL);
        $this->addArgument('version', InputArgument::OPTIONAL, 'The version to add or delete.');
        $this->addOption('add', null, InputOption::VALUE_NONE, 'Add the specified version.');
        $this->addOption('delete', null, InputOption::VALUE_NONE, 'Delete the specified version.');
        $this->addOption('all', null, InputOption::VALUE_NONE, 'Apply to all the versions.');
        $this->setHelp($help);

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workingDir = $this->getWorkingDir();
        $installDir = $this->getInstallDir();

        $config = $this->getConfiguration();

        if (!$this->io->getOption('add') && !$this->io->getOption('delete')) {
            $this->io->error('You must specify whether you want to --add or --delete the specified version.');

            return 1;
        }

        $packageName = $this->io->getArgument('package');
        $migrationConfigs = $this->getMigrationConfigs($config);

        if (!isset($migrationConfigs[$packageName])) {
            $this->io->error(sprintf('Unable to find migrations for the package "%s"', $packageName));

            return 1;
        }

        $this->io->note('Synchronizing versions manually is recommended for development use only');

        if ($this->io->isInteractive()) {
            $confirmation = $this->io->askConfirmation('Are you sure you wish to continue?', false);
            if (!$confirmation) {
                $this->io->error('Synchronization cancelled.');

                return 1;
            }
        }

        $markMigrated = (boolean) $input->getOption('add');

        if ($this->io->getOption('all') === true) {
            if ($markMigrated) {
                $result = $this->versionManager->markAllMigrated(
                    $workingDir,
                    $installDir,
                    $migrationConfigs[$packageName],
                    $this->type
                );
            } else {
                $result = $this->versionManager->markAllNotMigrated(
                    $workingDir,
                    $installDir,
                    $migrationConfigs[$packageName],
                    $this->type
                );
            }
        } else {
            if ($markMigrated) {
                $result = $this->versionManager->markMigrated(
                    $workingDir,
                    $installDir,
                    $migrationConfigs[$packageName],
                    $this->type,
                    $this->io->getArgument('version')
                );
            } else {
                $result = $this->versionManager->markNotMigrated(
                    $workingDir,
                    $installDir,
                    $migrationConfigs[$packageName],
                    $this->type,
                    $this->io->getArgument('version')
                );
            }
        }

        if (!$result) {
            return 1;
        }
    }
}
