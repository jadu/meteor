<?php

namespace Meteor\Patch\Task;

use Mockery;

class TaskBusTest extends \PHPUnit_Framework_TestCase
{
    private $taskBus;

    public function setUp()
    {
        $this->taskBus = new TaskBus();
    }

    public function testCallsRegisteredHandler()
    {
        $handler = Mockery::mock();
        $this->taskBus->registerHandler('stdClass', $handler);

        $task = new \stdClass();
        $config = ['name' => 'value'];

        $handler->shouldReceive('handle')
            ->with($task, $config)
            ->andReturn(true)
            ->once();

        $this->assertTrue($this->taskBus->run($task, $config));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionWhenMissingHandler()
    {
        $this->taskBus->run(new \stdClass(), ['name' => 'value']);
    }
}
