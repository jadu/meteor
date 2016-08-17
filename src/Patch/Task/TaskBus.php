<?php

namespace Meteor\Patch\Task;

use InvalidArgumentException;

class TaskBus implements TaskBusInterface
{
    /**
     * @var array
     */
    private $handlers = array();

    /**
     * @param string $className
     * @param string $handler
     */
    public function registerHandler($className, $handler)
    {
        $this->handlers[$className] = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function run($task, array $config)
    {
        $className = get_class($task);
        if (!array_key_exists($className, $this->handlers)) {
            throw new InvalidArgumentException(sprintf('Unable to find task handler for "%s"', $className));
        }

        return $this->handlers[$className]->handle($task, $config);
    }
}
