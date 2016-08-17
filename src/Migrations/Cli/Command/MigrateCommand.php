<?php

namespace Meteor\Migrations\Cli\Command;

use Meteor\IO\IOInterface;
use Meteor\Logger\LoggerInterface;
use Meteor\Migrations\Migrator;
use Meteor\Platform\PlatformInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends AbstractMigrationCommand
{
    /**
     * @var Migrator
     */
    private $migrator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $name
     * @param array $config
     * @param IOInterface $io
     * @param PlatformInterface $platform
     * @param Migrator $migrator
     * @param LoggerInterface $logger
     */
    public function __construct($name, array $config, IOInterface $io, PlatformInterface $platform, Migrator $migrator, LoggerInterface $logger, $type)
    {
        parent::__construct($name, $config, $io, $platform);

        $this->migrator = $migrator;
        $this->logger = $logger;
        $this->type = $type;
    }

    protected function configure()
    {
        $help = <<<EOT
The <info>%command.name%</info> command executes a migration to a specified version or the latest available version:

    <info>%command.full_name%</>

By default it will execute all sets of migrations within the package.

You can optionally specify the package name to execute just one set of migrations.

    <info>%command.full_name%</> jadu/cms

You can optionally also specify the version you wish to migrate to:

    <info>%command.full_name% jadu/cms YYYYMMDDHHMMSS</>
EOT;

        $this->setDescription('Execute a migration to a specified version or the latest available version.');
        $this->addArgument('package', InputArgument::OPTIONAL);
        $this->addArgument('version', InputArgument::OPTIONAL, 'The version number (YYYYMMDDHHMMSS) or alias (first, prev, next, latest) to migrate to.', 'latest');
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

        $this->logger->enable($this->getLogPath($workingDir));
        $config = $this->getConfiguration();
        $migrationConfigs = $this->getMigrationConfigs($config);
        $packageName = $this->io->getArgument('package');

        if ($packageName === null) {
            if (empty($migrationConfigs)) {
                $this->io->error('There are no migrations configured');

                return 1;
            }

            foreach ($migrationConfigs as $packageName => $migrationConfig) {
                $this->io->text(sprintf('Running <info>%s</> %s migrations', $packageName, $this->type));

                $result = $this->migrator->migrate(
                    $workingDir,
                    $installDir,
                    $migrationConfig,
                    $this->type,
                    'latest'
                );
            }

            if (!$result) {
                return 1;
            }

            return;
        }

        if (!isset($migrationConfigs[$packageName])) {
            $this->io->error(sprintf('Unable to find migrations for the package "%s"', $packageName));

            return 1;
        }

        $this->io->text(sprintf('Running <info>%s</> %s migrations', $packageName, $this->type));

        $result = $this->migrator->migrate(
            $workingDir,
            $installDir,
            $migrationConfigs[$packageName],
            $this->type,
            $this->io->getArgument('version')
        );

        if (!$result) {
            return 1;
        }
    }
}
