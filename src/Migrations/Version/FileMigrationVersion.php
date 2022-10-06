<?php

namespace Meteor\Migrations\Version;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Version\Executor;
use Doctrine\Migrations\Version\Version;

class FileMigrationVersion extends Version
{
    /**
     * @var FileMigrationVersionStorage
     */
    private $versionStorage;

    /**
     * @param Configuration $configuration
     * @param string $version
     * @param string $class
     * @param FileMigrationVersionStorage $versionStorage
     */
    public function __construct(Configuration $configuration, $version, $class, Executor $executor, FileMigrationVersionStorage $versionStorage = null)
    {
        parent::__construct($configuration, $version, $class, $executor);

        $this->versionStorage = $versionStorage;
    }

    public function markMigrated(): void
    {
        $this->versionStorage->markMigrated($this->getVersion());
    }

    public function markNotMigrated(): void
    {
        $this->versionStorage->markNotMigrated($this->getVersion());
    }
}
