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
     * Process each script type.
     *
     * @param string $scriptName
     * @return boolean
     */
    public function run($scriptName)
    {
        $result = true;

        if (isset($this->scripts['global'])) {
            $result = $this->runGlobal($scriptName);
        }

        if (isset($this->scripts['combined'])) {
            $result = $this->runCombined($scriptName);
        }

        return $result;
    }

    /**
     * Search through the global scripts and process.
     *
     * @param $scriptName
     * @return bool
     */
    private function runGlobal($scriptName)
    {
        $scripts = $this->scripts['global'];

        if (!isset($scripts[$scriptName])) {
            return false;
        }

        return $this->runProcessedScripts($scriptName, $scripts);
    }

    /**
     * Search through each of the combined scripts and process.
     *
     * @param $scriptName
     * @return bool
     */
    private function runCombined($scriptName)
    {
        $result = true;
        $products = $this->scripts['combined'];

        foreach ($products as $name => $scripts) {
            if (!isset($scripts[$scriptName])) {
                continue;
            }

            $result = $this->runProcessedScripts($scriptName, $scripts);
        }

        return $result;
    }

    /**
     * Handle the calling of the script processing.
     *
     * @param $scriptName
     * @param $scripts
     * @return bool
     */
    private function runProcessedScripts($scriptName, $scripts)
    {
        $result = true;
        foreach ($scripts[$scriptName] as $script) {
            $result = $this->runScript($scriptName, $script, $scripts);
        }
        return $result;
    }

    /**
     * Run a single script, or parse the script if it's a function alias.
     *
     * @param $scriptName
     * @param $script
     * @param $scripts
     * @return bool
     */
    private function runScript($scriptName, $script, $scripts)
    {
        if (strpos($script, '@') === 0) {
            $script = substr($script, 1);
            if ($scriptName === $script) {
                throw new RuntimeException(sprintf('Infinite recursion detected in script "%s"', $scriptName));
            }

            return $this->runProcessedScripts($script, $scripts);
        }

        $this->processRunner->run($script, $this->getWorkingDir());

        return true;
    }
}
