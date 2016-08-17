<?php

namespace Meteor\Migrations\Configuration;

interface JaduPathAwareConfigurationInterface
{
    /**
     * @return string
     */
    public function getJaduPath();
}
