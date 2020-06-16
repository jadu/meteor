<?php

namespace Meteor\Patch\Cli\Command;

use InvalidArgumentException;
use Meteor\Cli\Command\AbstractCommand;
use Meteor\IO\IOInterface;
use Meteor\Platform\PlatformInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractPatchCommand extends AbstractCommand
{
    /**
     * @var PlatformInterface
     */
    protected $platform;

    /**
     * @var string
     */
    protected $installDir;

    /**
     * @var string
     */
    protected $logDir;

    /**
     * @param string            $name
     * @param array             $config
     * @param IOInterface       $io
     * @param PlatformInterface $platform
     */
    public function __construct($name, array $config, IOInterface $io, PlatformInterface $platform)
    {
        parent::__construct($name, $config, $io);

        $this->platform = $platform;
    }

    protected function configure()
    {
        $this->addOption('install-dir', 'i', InputOption::VALUE_REQUIRED, 'The Jadu install directory');
        $this->addOption('log-dir', 'ld', InputOption::VALUE_REQUIRED, 'Optional log directory');
        $this->addOption('path', null, InputOption::VALUE_REQUIRED, '<fg=yellow>[DEPRECATED] Use the --install-dir/-i option instead</>');
        $this->addOption('db-name', null, InputOption::VALUE_REQUIRED, 'The name of the database');
        $this->addOption('db-user', null, InputOption::VALUE_REQUIRED, 'The username used to connect to the database');
        $this->addOption('db-password', null, InputOption::VALUE_REQUIRED, 'The password used to connect to the database');
        $this->addOption('db-host', null, InputOption::VALUE_REQUIRED, 'The host of the database');
        $this->addOption('db-driver', null, InputOption::VALUE_REQUIRED, 'The database driver');
    }

    /**
     * @return string
     */
    protected function getInstallDir()
    {
        if ($this->installDir === null) {
            $this->installDir = rtrim($this->io->getOption('install-dir'), '/');
            if ($this->installDir === '') {
                // Fallback to the old --path option
                $this->installDir = rtrim($this->io->getOption('path'), '/');
            }

            if (trim($this->installDir) === '' && $this->io->isInteractive()) {
                $this->installDir = $this->io->ask('Enter the Jadu installation path', $this->platform->getDefaultInstallDir());
            }

            if (trim($this->installDir) === '') {
                throw new InvalidArgumentException('Missing the required --install-dir option.');
            }

            if (!is_dir($this->installDir)) {
                throw new InvalidArgumentException(sprintf('The install directory `%s` does not exist.', $this->installDir));
            }
        }

        return $this->installDir;
    }

    /**
     * @param string $workingDir
     *
     * @return string
     */
    protected function getLogPath($workingDir)
    {
        $filename = 'meteor-' . date('YmdHis') . '.log';
        $logDir = rtrim($this->io->getOption('log-dir'), '/');

        if (!$logDir) {
            // Store logs in the current working directory
            return $workingDir . '/logs/' . $filename;
        }

        if (!is_dir($logDir)) {
            throw new InvalidArgumentException(sprintf('The log directory `%s` does not exist.', $logDir));
        }

        return $logDir . '/' . $filename;
    }
}
