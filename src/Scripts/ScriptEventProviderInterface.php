<?php

namespace Meteor\Scripts;

interface ScriptEventProviderInterface
{
    /**
     * @return array
     */
    public function getEventNames();
}
