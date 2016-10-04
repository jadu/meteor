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
        $result = false;

        foreach ($this->scripts as $scripts) {
            if (!isset($scripts[$scriptName])) {
                continue;
            }

            $result = $this->runScripts($scriptName, $scripts);
        }

        return $result;
    }

    /**
     * Cycle through each of the script declarations and run the, or
     * parse the script if it's a function alias.
     *
     * @param $scriptName
     * @param $scripts
     * @return bool
     */
    private function runScripts($scriptName, $scripts)
    {
        foreach ($scripts[$scriptName] as $script) {
            if (strpos($script, '@') === 0) {
                $script = substr($script, 1);
                if ($scriptName === $script) {
                    throw new RuntimeException(sprintf('Infinite recursion detected in script "%s"', $scriptName));
                }

                return $this->runScripts($script, $scripts);
            }

            $this->processRunner->run($script, $this->getWorkingDir());
        }

        return true;
    }

}
