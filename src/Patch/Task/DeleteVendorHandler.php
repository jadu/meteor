<?php

namespace Meteor\Patch\Task;

use Meteor\Filesystem\Filesystem;
use Meteor\IO\IOInterface;

class DeleteVendorHandler
{
    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param IOInterface $io
     * @param Filesystem $filesystem
     */
    public function __construct(IOInterface $io, Filesystem $filesystem)
    {
        $this->io = $io;
        $this->filesystem = $filesystem;
    }

    /**
     * @param DeleteBackup $task
     */
    public function handle(DeleteVendor $task)
    {
        $this->io->text(sprintf('Removing the vendor <info>%s</>', $task->getVendorFolder()));
        $this->filesystem->remove($task->getVendorFolder());
    }
}
