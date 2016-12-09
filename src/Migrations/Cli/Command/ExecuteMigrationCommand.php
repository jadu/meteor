<?php

namespace Meteor\Migrations\Cli\Command;

use Meteor\IO\IOInterface;
use Meteor\Logger\LoggerInterface;
use Meteor\Migrations\Migrator;
use Meteor\Patch\Cli\Command\AbstractPatchCommand;
use Meteor\Platform\PlatformInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteMigrationCommand extends AbstractPatchCommand
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
        $help = <<<'EOT'
The <info>%command.name%</info> command executes a single migration version up or down manually:

    <info>%command.full_name% jadu/cms YYYYMMDDHHMMSS</info>

If no <comment>--up</comment> or <comment>--down</comment> option is specified it defaults to up:

    <info>%command.full_name% jadu/cms YYYYMMDDHHMMSS --down</info>

You can also execute the migration as a <comment>--dry-run</comment>:

    <info>%command.full_name% jadu/cms YYYYMMDDHHMMSS --dry-run</info>

You can output the would be executed SQL statements to a file with <comment>--write-sql</comment>:

    <info>%command.full_name% jadu/cms YYYYMMDDHHMMSS --write-sql</info>
EOT;

        $this->setDescription('Execute a single migration version up or down manually.');
        $this->addArgument('package', InputArgument::REQUIRED);
        $this->addArgument('version', InputArgument::OPTIONAL, 'The version number (YYYYMMDDHHMMSS) to execute.');
        $this->addOption('up', null, InputOption::VALUE_NONE, 'Execute the migration up.');
        $this->addOption('down', null, InputOption::VALUE_NONE, 'Execute the migration down.');
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

        $packageName = $this->io->getArgument('package');
        if (empty($packageName)) {
            $this->io->error('You must specify a package name as the first argument.');

            return 1;
        }

        $config = $this->getConfiguration();

        if (!isset($config[$packageName])) {
            $this->io->error(sprintf('Unable to find migrations for the package "%s"', $packageName));

            return 1;
        }

        $this->logger->enable($this->getLogPath($workingDir));

        $version = $this->io->getArgument('version');
        $direction = $this->io->getOption('down') ? 'down' : 'up';

        $this->io->note('Executing individual migrations is recommended for development use only');

        if ($this->io->isInteractive()) {
            $confirmation = $this->io->askConfirmation('Are you sure you wish to continue?', false);
            if (!$confirmation) {
                $this->io->error('Migration cancelled.');

                return 1;
            }
        }

        $result = $this->migrator->execute(
            $workingDir,
            $installDir,
            $config[$packageName],
            $this->type,
            $version,
            $direction
        );

        if (!$result) {
            return 1;
        }
    }
}
