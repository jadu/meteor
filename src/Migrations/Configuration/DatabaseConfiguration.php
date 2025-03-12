<?php

namespace Meteor\Migrations\Configuration;

use Doctrine\Migrations\Version\Version;

class DatabaseConfiguration extends AbstractConfiguration implements JaduPathAwareConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    protected function createMigration($version, $class)
    {
        return new Version($this, $version, $class, $this->getDependencyFactory()->getVersionExecutor());
    }
}
