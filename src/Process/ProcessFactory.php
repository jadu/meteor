<?php

namespace Meteor\Process;

use Symfony\Component\Process\Process;

class ProcessFactory
{
    /**
     * @return Process
     */
    public function create($command)
    {
        return new Process($command);
    }
}
