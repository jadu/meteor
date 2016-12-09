<?php

namespace Meteor\Migrations\Version;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Provider\SchemaDiffProviderInterface;
use Doctrine\DBAL\Migrations\Version;

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
     * @param SchemaDiffProviderInterface $schemaProvider
     * @param FileMigrationVersionStorage $versionStorage
     */
    public function __construct(Configuration $configuration, $version, $class, SchemaDiffProviderInterface $schemaProvider = null, FileMigrationVersionStorage $versionStorage = null)
    {
        parent::__construct($configuration, $version, $class);

        $this->versionStorage = $versionStorage;
    }

    public function markMigrated()
    {
        $this->versionStorage->markMigrated($this->getVersion());
    }

    public function markNotMigrated()
    {
        $this->versionStorage->markNotMigrated($this->getVersion());
    }
}
