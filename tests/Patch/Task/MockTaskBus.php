<?php

namespace Meteor\Patch\Task;

class MockTaskBus implements TaskBusInterface
{
    public $handled = [];

    public function run($task, array $config)
    {
        $this->handled[] = $task;
    }
}
