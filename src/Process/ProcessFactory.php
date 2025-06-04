<?php

namespace Meteor\Process;

use Symfony\Component\Process\Process;

class ProcessFactory
{
    /**
     * @param string $command
     * @param string $cwd
     *
     * @return Process
     */
    public function create(string $command, ?string $cwd = null)
    {
        return Process::fromShellCommandline($command, $cwd);
    }
}
