<?php

namespace Meteor\Process;

use Meteor\IO\IOInterface;
use RuntimeException;

class ProcessRunner
{
    const DEFAULT_TIMEOUT = 3600;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var MemoryLimitSetter
     */
    private $memoryLimitSetter;

    /**
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * @param IOInterface $io
     * @param MemoryLimitSetter $memoryLimitSetter
     * @param ProcessFactory $processFactory
     */
    public function __construct(
        IOInterface $io,
        MemoryLimitSetter $memoryLimitSetter,
        ProcessFactory $processFactory
    ) {
        $this->io = $io;
        $this->memoryLimitSetter = $memoryLimitSetter;
        $this->processFactory = $processFactory;
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
        if ($this->memoryLimitSetter->isPHPScript($command) &&
            !$this->memoryLimitSetter->hasMemoryLimit($command)) {
            $command = $this->memoryLimitSetter->setMemoryLimit($command);
        }

        $process = $this->processFactory->create($command);
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
