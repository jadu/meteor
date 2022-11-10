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
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * @param IOInterface $io
     * @param ProcessFactory $processFactory
     */
    public function __construct(
        IOInterface $io,
        ProcessFactory $processFactory
    ) {
        $this->io = $io;
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
        if (PHPMemoryLimitSetter::isPHPScript($command) && !PHPMemoryLimitSetter::hasMemoryLimit($command)) {
            $command = PHPMemoryLimitSetter::setMemoryLimit($command);
        }
        if ($cwd === null) {
            $cwd = getcwd();
        }
        $process = $this->processFactory->create(explode(' ', $command));
        $process->setWorkingDirectory($cwd);
        $process->setTimeout($timeout);

        $this->io->debug(sprintf('Running command "%s" in "%s"', $command, $cwd));

        $process->run($callback);

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        return trim($process->getOutput());
    }
}
