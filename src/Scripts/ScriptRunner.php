<?php

namespace Meteor\Scripts;

use Meteor\IO\IOInterface;
use Meteor\Process\ProcessRunner;

class ScriptRunner
{
    /**
     * @var ProcessRunner
     */
    private $processRunner;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var array
     */
    private $scripts;

    /**
     * @var string
     */
    private $workingDir;

    /**
     * @param ProcessRunner $processRunner
     * @param IOInterface $io
     * @param array $scripts
     */
    public function __construct(ProcessRunner $processRunner, IOInterface $io, array $scripts)
    {
        $this->processRunner = $processRunner;
        $this->io = $io;
        $this->scripts = $scripts;
    }

    /**
     * @return string
     */
    public function getWorkingDir()
    {
        return $this->workingDir;
    }

    /**
     * @param string $workingDir
     */
    public function setWorkingDir($workingDir)
    {
        $this->workingDir = $workingDir;
    }

    /**
     * Process each script type.
     *
     * @param string $scriptName
     */
    public function run($scriptName)
    {
        foreach ($this->scripts as $scripts) {
            if (isset($scripts[$scriptName]) && !empty($scripts[$scriptName])) {
                $this->io->text(sprintf('Running scripts for "%s"', $scriptName));
                $this->io->progressStart(count($scripts[$scriptName]));
                $this->runScripts($scriptName, $scripts);
                $this->io->progressFinish();
            }
        }
    }

    /**
     * Cycle through each of the script declarations and run the, or
     * parse the script if it's a function alias.
     *
     * @param $scriptName
     * @param $scripts
     */
    private function runScripts($scriptName, $scripts)
    {
        foreach ($scripts[$scriptName] as $script) {
            if (strpos($script, '@') === 0) {
                // NB: Infinite recursion detection happens when processing the config
                $script = substr($script, 1);
                $this->runScripts($script, $scripts);
            } else {
                $this->processRunner->run($script, $this->getWorkingDir());
                $this->io->progressAdvance();
            }
        }
        $this->io->newLine();
    }
}
