<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use Meteor\Migrations\Configuration\ConfigurationFactory;
use Meteor\Migrations\MigrationsConstants;
use Meteor\Migrations\Version\VersionFileManager;
use Mockery;

class UpdateMigrationVersionFilesHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $configurationFactory;
    private $versionFileManager;
    private $handler;

    public function setUp()
    {
        $this->configurationFactory = Mockery::mock('Meteor\Migrations\Configuration\ConfigurationFactory');
        $this->versionFileManager = Mockery::mock('Meteor\Migrations\Version\VersionFileManager');
        $this->handler = new UpdateMigrationVersionFilesHandler(
            $this->configurationFactory,
            $this->versionFileManager,
            new NullIO()
        );
    }

    public function testSetsCurrentVersion()
    {
        $config = array(
            'name' => 'root',
            'migrations' => array(
                'table' => 'Migrations',
            ),
        );

        $databaseConfiguration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration', array(
            'getCurrentVersion' => '20160701000000',
        ));

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config['migrations'], 'patch', 'install')
            ->andReturn($databaseConfiguration)
            ->once();

        $this->versionFileManager->shouldReceive('setCurrentVersion')
            ->with('20160701000000', 'backup', 'Migrations', VersionFileManager::DATABASE_MIGRATION)
            ->once();

        $fileConfiguration = Mockery::mock('Meteor\Migrations\Configuration\FileConfiguration', array(
            'getCurrentVersion' => '20160701000000',
        ));

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_FILE, $config['migrations'], 'patch', 'install')
            ->andReturn($fileConfiguration)
            ->once();

        $this->versionFileManager->shouldReceive('setCurrentVersion')
            ->with('20160701000000', 'backup', 'Migrations', VersionFileManager::FILE_MIGRATION)
            ->once();

        $this->handler->handle(new UpdateMigrationVersionFiles('backup', 'patch', 'install'), $config);
    }

    public function testSetsCurrentVersionForCombinedPackageMigrations()
    {
        $config = array(
            'combined' => array(
                array(
                    'name' => 'test',
                    'migrations' => array(
                        'table' => 'Migrations',
                    ),
                ),
            ),
        );

        $databaseConfiguration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration', array(
            'getCurrentVersion' => '20160701000000',
        ));

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config['combined'][0]['migrations'], 'patch', 'install')
            ->andReturn($databaseConfiguration)
            ->once();

        $this->versionFileManager->shouldReceive('setCurrentVersion')
            ->with('20160701000000', 'backup', 'Migrations', VersionFileManager::DATABASE_MIGRATION)
            ->once();

        $fileConfiguration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration', array(
            'getCurrentVersion' => '20160701000000',
        ));

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_FILE, $config['combined'][0]['migrations'], 'patch', 'install')
            ->andReturn($fileConfiguration)
            ->once();

        $this->versionFileManager->shouldReceive('setCurrentVersion')
            ->with('20160701000000', 'backup', 'Migrations', VersionFileManager::FILE_MIGRATION)
            ->once();

        $this->handler->handle(new UpdateMigrationVersionFiles('backup', 'patch', 'install'), $config);
    }
}
