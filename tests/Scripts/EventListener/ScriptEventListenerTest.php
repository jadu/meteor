<?php

namespace Meteor\Scripts\EventListener;

use Meteor\Scripts\ScriptRunner;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

class ScriptEventListenerTest extends TestCase
{
    private $scriptRunner;
    private $listener;

    protected function setUp(): void
    {
        $this->scriptRunner = Mockery::mock(ScriptRunner::class);
        $this->listener = new ScriptEventListener($this->scriptRunner);
    }

    public function testHandleEvent()
    {
        $event = new Event();

        $this->scriptRunner->shouldReceive('run')
            ->with('test')
            ->once();

        $this->listener->handleEvent($event, 'test');
    }
}
