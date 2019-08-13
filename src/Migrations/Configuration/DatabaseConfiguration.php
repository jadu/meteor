<?php

namespace Meteor\Migrations\Configuration;

use Doctrine\DBAL\Migrations\Version;

class DatabaseConfiguration extends AbstractConfiguration implements JaduPathAwareConfigurationInterface, DebugLoggerIOConsoleAwareConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    protected function createMigration($version, $class)
    {
        return new Version($this, $version, $class);
    }
}
