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
     * @param string $scriptName
     */
    public function run($scriptName)
    {
        if (!isset($this->scripts[$scriptName])) {
            return false;
        }

        $result = true;
        foreach ($this->scripts[$scriptName] as $script) {
            $result = $this->runScript($script);
        }

        return $result;
    }

    private function runScript($script)
    {
        if (strpos($script, '@') === 0) {
            // NB: Infinite recursion detection happens when processing the config
            return $this->run(substr($script, 1));
        }

        $this->processRunner->run($script, $this->getWorkingDir());

        return true;
    }
}
