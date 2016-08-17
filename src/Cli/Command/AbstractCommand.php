<?php

namespace Meteor\Cli\Command;

use InvalidArgumentException;
use Meteor\IO\IOInterface;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var string
     */
    protected $workingDir;

    /**
     * @param string $name
     * @param array $config
     * @param IOInterface $io
     */
    public function __construct($name, array $config, IOInterface $io)
    {
        parent::__construct($name);

        $this->config = $config;
        $this->io = $io;
    }

    /**
     * @return IOInterface
     */
    public function getIO()
    {
        return $this->io;
    }

    /**
     * @param IOInterface $io
     */
    public function setIO(IOInterface $io)
    {
        $this->io = $io;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfiguration(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    protected function getWorkingDir()
    {
        if ($this->workingDir === null) {
            $this->workingDir = rtrim($this->io->getOption('working-dir'), '/');

            if ($this->workingDir === '') {
                // Fallback to the old --patch-path option
                $this->workingDir = rtrim($this->io->getOption('patch-path'), '/');
                if ($this->workingDir === '') {
                    // Lastly fallback to the current directory
                    $this->workingDir = getcwd();
                }
            }

            if (!is_dir($this->workingDir)) {
                throw new InvalidArgumentException(sprintf('The working directory `%s` does not exist.', $this->workingDir));
            }
        }

        return $this->workingDir;
    }
}
