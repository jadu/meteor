<?php

namespace Meteor\Scripts\EventListener;

use Meteor\Scripts\ScriptRunner;
use Symfony\Component\EventDispatcher\Event;

class ScriptEventListener
{
    /**
     * @var ScriptRunner
     */
    private $scriptRunner;

    /**
     * @param ScriptRunner $scriptRunner
     */
    public function __construct(ScriptRunner $scriptRunner)
    {
        $this->scriptRunner = $scriptRunner;
    }

    /**
     * @param Event $event
     */
    public function handleEvent(Event $event)
    {
        $this->scriptRunner->run($event->getName());
    }
}
