<?php

namespace Meteor\Scripts\EventListener;

use Mockery;
use Symfony\Component\EventDispatcher\Event;

class ScriptEventListenerTest extends \PHPUnit_Framework_TestCase
{
    private $scriptRunner;
    private $listener;

    public function setUp()
    {
        $this->scriptRunner = Mockery::mock('Meteor\Scripts\ScriptRunner');
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
