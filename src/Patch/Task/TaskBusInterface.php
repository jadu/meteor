<?php

namespace Meteor\Patch\Task;

interface TaskBusInterface
{
    /**
     * @param mixed $task
     *
     * @return mixed
     */
    public function run($task, array $config);
}
