<?php

namespace Meteor\Scripts\Cli\Command;

use Meteor\Cli\Command\AbstractCommand;
use Meteor\IO\IOInterface;
use Meteor\Scripts\ScriptRunner;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends AbstractCommand
{
    /**
     * @var ScriptRunner
     */
    private $scriptRunner;

    /**
     * @param string $name
     * @param array $config
     * @param IOInterface $io
     * @param ScriptRunner $scriptRunner
     */
    public function __construct($name, array $config, IOInterface $io, ScriptRunner $scriptRunner)
    {
        parent::__construct($name, $config, $io);

        $this->scriptRunner = $scriptRunner;
    }

    protected function configure()
    {
        $this->setName('run');
        $this->setDescription('Runs a script.');
        $this->addArgument('script', InputArgument::REQUIRED, 'The name of the script to run');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->scriptRunner->setWorkingDir($this->getWorkingDir());

        $scriptName = $this->io->getArgument('script');
        $this->scriptRunner->run($scriptName);
    }
}
