<?php

namespace Meteor\Patch\Task;

use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;

class TaskBusTest extends TestCase
{
    private $taskBus;

    protected function setUp(): void
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

    public function testThrowsExceptionWhenMissingHandler()
    {
        static::expectException(InvalidArgumentException::class);

        $this->taskBus->run(new \stdClass(), ['name' => 'value']);
    }
}
