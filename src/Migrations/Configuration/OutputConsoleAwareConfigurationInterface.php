<?php

namespace Meteor\Migrations\Configuration;

interface OutputConsoleAwareConfigurationInterface
{
    /**
     * @return string
     */
    public function getOutput();
}
