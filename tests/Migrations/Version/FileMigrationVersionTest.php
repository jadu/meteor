<?php

namespace Meteor\Migrations\Version;

use Mockery;

class FileMigrationVersionTest extends \PHPUnit_Framework_TestCase
{
    private $configuration;
    private $versionStorage;
    private $version;

    public function setUp()
    {
        $this->configuration = Mockery::mock('Doctrine\DBAL\Migrations\Configuration\Configuration', [
            'getConnection' => Mockery::mock('Doctrine\DBAL\Connection', [
                'getDatabasePlatform' => Mockery::mock('Doctrine\DBAL\Platforms\AbstractPlatform'),
                'getSchemaManager' => Mockery::mock('Doctrine\DBAL\Schema\AbstractSchemaManager'),
            ]),
            'getOutputWriter' => Mockery::mock('Doctrine\DBAL\Migrations\OutputWriter'),
        ]);
        $this->versionStorage = Mockery::mock('Meteor\Migrations\Version\FileMigrationVersionStorage');
        $this->version = new FileMigrationVersion($this->configuration, '12345', 'stdClass', $this->versionStorage);
    }

    public function testMarkMigrated()
    {
        $this->versionStorage->shouldReceive('markMigrated')
            ->with('12345')
            ->once();

        $this->version->markMigrated();
    }

    public function testMarkNotMigrated()
    {
        $this->versionStorage->shouldReceive('markNotMigrated')
            ->with('12345')
            ->once();

        $this->version->markNotMigrated();
    }
}
