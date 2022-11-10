<?php

namespace Meteor\Migrations\Version;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\OutputWriter;
use Doctrine\Migrations\ParameterFormatterInterface;
use Doctrine\Migrations\Provider\SchemaDiffProviderInterface;
use Doctrine\Migrations\Stopwatch;
use Doctrine\Migrations\Version\Executor;
use Mockery;
use PHPUnit\Framework\TestCase;

class FileMigrationVersionTest extends TestCase
{
    private $configuration;
    private $versionStorage;
    private $version;

    protected function setUp(): void
    {
        $this->configuration = Mockery::mock(Configuration::class, [
            'getConnection' => Mockery::mock(Connection::class, [
                'getDatabasePlatform' => Mockery::mock(AbstractPlatform::class),
                'getSchemaManager' => Mockery::mock(AbstractSchemaManager::class),
            ]),
            'getOutputWriter' => Mockery::mock(OutputWriter::class),
        ]);
        $this->executor = new Executor(
            Mockery::mock(Configuration::class),
            Mockery::mock(Connection::class),
            Mockery::mock(SchemaDiffProviderInterface::class),
            Mockery::mock(OutputWriter::class),
            Mockery::mock(ParameterFormatterInterface::class),
            Mockery::mock(Stopwatch::class)
        );
        $this->versionStorage = Mockery::mock(FileMigrationVersionStorage::class);
        $this->version = new FileMigrationVersion(
            $this->configuration,
            '12345',
            'stdClass',
            $this->executor,
            $this->versionStorage
        );
    }

    public function testMarkMigrated()
    {
        $this->versionStorage->shouldReceive('markMigrated')
            ->andReturnUsing(function ($version) {
                static::assertEquals('12345', $version);
            })
            ->once();

        $this->version->markMigrated();
    }

    public function testMarkNotMigrated()
    {
        $this->versionStorage->shouldReceive('markNotMigrated')
            ->andReturnUsing(function ($version) {
                static::assertEquals('12345', $version);
            })
            ->once();

        $this->version->markNotMigrated();
    }
}
