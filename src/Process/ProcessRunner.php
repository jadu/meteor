<?php

namespace Meteor\Process;

use Meteor\IO\IOInterface;
use RuntimeException;
use Symfony\Component\Process\Process;

class ProcessRunner
{
    const DEFAULT_TIMEOUT = 3600;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param IOInterface $io
     */
    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }

    /**
     * @param string $command
     * @param string $cwd
     * @param callable $callback
     * @param int $timeout
     *
     * @throws RuntimeException
     *
     * @return string
     */
    public function run($command, $cwd = null, $callback = null, $timeout = self::DEFAULT_TIMEOUT)
    {
        $process = new Process($command);
        $process->setWorkingDirectory($cwd);
        $process->setTimeout($timeout);

        $this->io->debug(sprintf('Running command "%s" in "%s"', $command, $cwd !== null ? $cwd : getcwd()));

        $process->run($callback);

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        return trim($process->getOutput());
    }
}
