<?php

namespace Meteor\Scripts\EventListener;

use Meteor\Scripts\ScriptRunner;
use Symfony\Contracts\EventDispatcher\Event;

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
     * @param string $eventName
     */
    public function handleEvent(Event $event, $eventName)
    {
        $this->scriptRunner->run($eventName);
    }
}
