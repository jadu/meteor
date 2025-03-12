<?php

namespace Meteor\Migrations\Configuration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\Migrations\Exception\DuplicateMigrationVersion;
use Mockery;
use PHPUnit\Framework\TestCase;
use stdClass;

class DatabaseConfigurationTest extends TestCase
{
    private $configuration;

    protected function setUp(): void
    {
        $this->configuration = new DatabaseConfiguration(Mockery::mock(Connection::class, [
            'getSchemaManager' => Mockery::mock(AbstractSchemaManager::class),
            'getDatabasePlatform' => Mockery::mock(AbstractPlatform::class)
        ]));
    }

    /**
     * Ensure the method exists as it is used by old migrations.
     */
    public function testGetSetJaduPath()
    {
        $this->configuration->setJaduPath('/var/www/jadu');

        static::assertSame('/var/www/jadu', $this->configuration->getJaduPath());
    }

    public function testRegisterMigrationThrowsForDuplicate()
    {
        $this->expectException(DuplicateMigrationVersion::class);
        $this->expectExceptionMessage('Migration version 123 already registered with class Doctrine\Migrations\Version\Version');

        $this->configuration->registerMigration('123', stdClass::class);
        $this->configuration->registerMigration('123', stdClass::class);
    }

    public function testGetVersion()
    {
        $this->expectException(\Doctrine\Migrations\Exception\UnknownMigrationVersion::class);
        $this->expectExceptionMessage('Could not find migration version 1234');

        $this->configuration->getVersion('1234');
    }
}
