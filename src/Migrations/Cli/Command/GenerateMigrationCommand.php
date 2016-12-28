<?php

namespace Meteor\Migrations\Cli\Command;

use Meteor\Cli\Command\AbstractCommand;
use Meteor\IO\IOInterface;
use Meteor\Migrations\Configuration\FileConfiguration;
use Meteor\Migrations\Generator\MigrationGenerator;
use Meteor\Migrations\MigrationsConstants;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateMigrationCommand extends AbstractCommand
{
    /**
     * @var MigrationGenerator
     */
    private $migrationGenerator;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $name
     * @param array $config
     * @param IOInterface $io
     * @param MigrationGenerator $migrationGenerator
     */
    public function __construct($name, array $config, IOInterface $io, MigrationGenerator $migrationGenerator, $type)
    {
        parent::__construct($name, $config, $io);

        $this->migrationGenerator = $migrationGenerator;
        $this->type = $type;
    }

    protected function configure()
    {
        $this->setDescription('Generates a new migration.');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getConfiguration();

        if (!isset($config['migrations'])) {
            $this->io->error('Migrations not configured');
        }

        $path = $this->getWorkingDir() . '/' . $config['migrations']['directory'];
        if ($this->type === MigrationsConstants::TYPE_FILE) {
            $path .= '/' . FileConfiguration::MIGRATION_DIRECTORY;
        }

        $timestamp = date('YmdHis');
        $this->migrationGenerator->generate($timestamp, $config['migrations']['namespace'], $path);

        $this->io->success(sprintf('Generated migration in %s/Version%s.php', $path, $timestamp));
    }
}
