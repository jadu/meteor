<?php

namespace Meteor\Scripts;

use Meteor\IO\IOInterface;
use Meteor\Process\ProcessRunner;
use RuntimeException;

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
            $result = $this->runScript($scriptName, $script);
        }

        return $result;
    }

    private function runScript($scriptName, $script)
    {
        if (strpos($script, '@') === 0) {
            $script = substr($script, 1);
            if ($scriptName === $script) {
                throw new RuntimeException(sprintf('Infinite recursion detected in script "%s"', $scriptName));
            }

            return $this->run($script);
        }

        $this->processRunner->run($script, $this->getWorkingDir());

        return true;
    }
}
