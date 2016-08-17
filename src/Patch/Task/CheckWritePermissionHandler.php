<?php

namespace Meteor\Patch\Task;

use Meteor\IO\IOInterface;

class CheckWritePermissionHandler
{
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
     * @param CheckWritePermission $task
     *
     * @return bool
     */
    public function handle(CheckWritePermission $task)
    {
        if (!is_dir($task->targetDir) || !is_writeable($task->targetDir)) {
            $this->io->error(sprintf('Unable to write to "%s"', $task->targetDir));

            return false;
        }

        return true;
    }
}
