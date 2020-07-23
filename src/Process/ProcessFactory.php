<?php

namespace Meteor\Process;

use Symfony\Component\Process\Process;

/**
 * Class ProcessFactory
 *
 * @author Jadu Ltd.
 */
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
