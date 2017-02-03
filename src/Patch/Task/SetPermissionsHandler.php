<?php

namespace Meteor\Patch\Task;

use Meteor\IO\IOInterface;
use Meteor\Permissions\PermissionSetter;

class SetPermissionsHandler
{
    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var PermissionSetter
     */
    private $permissionSetter;

    /**
     * @param IOInterface $io
     * @param PermissionSetter $permissionSetter
     */
    public function __construct(IOInterface $io, PermissionSetter $permissionSetter)
    {
        $this->io = $io;
        $this->permissionSetter = $permissionSetter;
    }

    /**
     * @param SetPermissions $task
     * @param array $config
     */
    public function handle(SetPermissions $task, array $config)
    {
        $this->io->text(sprintf('Setting permissions on patched files in <info>%s</>', $task->targetDir));

        $this->permissionSetter->setPermissions($task->sourceDir, $task->targetDir);
    }
}
