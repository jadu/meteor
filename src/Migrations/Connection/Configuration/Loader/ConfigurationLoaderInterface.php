<?php

namespace Meteor\Migrations\Connection\Configuration\Loader;

interface ConfigurationLoaderInterface
{
    /**
     * @param string $installDir
     * @param array $configuration
     */
    public function load($installDir, array $configuration = []);
}
