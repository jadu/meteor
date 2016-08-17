<?php

namespace Meteor\Migrations\Version;

use Meteor\IO\NullIO;
use Meteor\Migrations\Configuration\ConfigurationFactory;
use Meteor\Migrations\MigrationsConstants;
use Mockery;

class VersionManagerTest extends \PHPUnit_Framework_TestCase
{
    private $configurationFactory;
    private $versionManager;

    public function setUp()
    {
        $this->configurationFactory = Mockery::mock('Meteor\Migrations\Configuration\ConfigurationFactory');
        $this->versionManager = new VersionManager($this->configurationFactory, new NullIO());
    }

    private function createVersion($versionString)
    {
        return Mockery::mock('Doctrine\DBAL\Migrations\Version', array(
            'getVersion' => $versionString,
            '__toString' => $versionString,
            'getTime' => 5,
        ));
    }

    public function testMarkMigrated()
    {
        $config = array();

        $version = $this->createVersion('20160701000000');

        $configuration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration');

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, 'patch', 'install')
            ->andReturn($configuration)
            ->once();

        $configuration->shouldReceive('hasVersion')
            ->with('20160701000000')
            ->andReturn(true)
            ->once();

        $configuration->shouldReceive('getVersion')
            ->with('20160701000000')
            ->andReturn($version)
            ->once();

        $configuration->shouldReceive('hasVersionMigrated')
            ->with($version)
            ->andReturn(false)
            ->once();

        $version->shouldReceive('markMigrated')
            ->once();

        $this->assertTrue($this->versionManager->markMigrated('patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, '20160701000000'));
    }

    public function testMarkMigratedReturnsFalseWhenVersionHasBeenMigrated()
    {
        $config = array();

        $version = $this->createVersion('20160701000000');

        $configuration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration');

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, 'patch', 'install')
            ->andReturn($configuration)
            ->once();

        $configuration->shouldReceive('hasVersion')
            ->with('20160701000000')
            ->andReturn(true)
            ->once();

        $configuration->shouldReceive('getVersion')
            ->with('20160701000000')
            ->andReturn($version)
            ->once();

        $configuration->shouldReceive('hasVersionMigrated')
            ->with($version)
            ->andReturn(true)
            ->once();

        $this->assertFalse($this->versionManager->markMigrated('patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, '20160701000000'));
    }

    public function testMarkMigratedReturnsFalseWhenVersionNotFound()
    {
        $config = array();

        $version = $this->createVersion('20160701000000');

        $configuration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration');

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, 'patch', 'install')
            ->andReturn($configuration)
            ->once();

        $configuration->shouldReceive('hasVersion')
            ->with('20160701000000')
            ->andReturn(false)
            ->once();

        $this->assertFalse($this->versionManager->markMigrated('patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, '20160701000000'));
    }

    public function testMarkNotMigrated()
    {
        $config = array();

        $version = $this->createVersion('20160701000000');

        $configuration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration');

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, 'patch', 'install')
            ->andReturn($configuration)
            ->once();

        $configuration->shouldReceive('hasVersion')
            ->with('20160701000000')
            ->andReturn(true)
            ->once();

        $configuration->shouldReceive('getVersion')
            ->with('20160701000000')
            ->andReturn($version)
            ->once();

        $configuration->shouldReceive('hasVersionMigrated')
            ->with($version)
            ->andReturn(true)
            ->once();

        $version->shouldReceive('markNotMigrated')
            ->once();

        $this->assertTrue($this->versionManager->markNotMigrated('patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, '20160701000000'));
    }

    public function testMarkNotMigratedReturnsFalseWhenVersionHasNotBeenMigrated()
    {
        $config = array();

        $version = $this->createVersion('20160701000000');

        $configuration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration');

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, 'patch', 'install')
            ->andReturn($configuration)
            ->once();

        $configuration->shouldReceive('hasVersion')
            ->with('20160701000000')
            ->andReturn(true)
            ->once();

        $configuration->shouldReceive('getVersion')
            ->with('20160701000000')
            ->andReturn($version)
            ->once();

        $configuration->shouldReceive('hasVersionMigrated')
            ->with($version)
            ->andReturn(false)
            ->once();

        $this->assertFalse($this->versionManager->markNotMigrated('patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, '20160701000000'));
    }

    public function testMarkNotMigratedReturnsFalseWhenVersionNotFound()
    {
        $config = array();

        $version = $this->createVersion('20160701000000');

        $configuration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration');

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, 'patch', 'install')
            ->andReturn($configuration)
            ->once();

        $configuration->shouldReceive('hasVersion')
            ->with('20160701000000')
            ->andReturn(false)
            ->once();

        $this->assertFalse($this->versionManager->markNotMigrated('patch', 'install', $config, MigrationsConstants::TYPE_DATABASE, '20160701000000'));
    }

    public function testMarkAllMigrated()
    {
        $config = array();

        $version1 = $this->createVersion('20160701000000');
        $version2 = $this->createVersion('20160702000000');
        $version3 = $this->createVersion('20160703000000');

        $configuration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration', array(
            'getAvailableVersions' => array(
                $version1->getVersion(),
                $version2->getVersion(),
                $version3->getVersion(),
            ),
        ));

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, 'patch', 'install')
            ->andReturn($configuration)
            ->once();

        $configuration->shouldReceive('getVersion')
            ->with($version1->getVersion())
            ->andReturn($version1)
            ->once();

        $configuration->shouldReceive('hasVersionMigrated')
            ->with($version1)
            ->andReturn(false)
            ->once();

        $version1->shouldReceive('markMigrated')
            ->once();

        $configuration->shouldReceive('getVersion')
            ->with($version2->getVersion())
            ->andReturn($version2)
            ->once();

        $configuration->shouldReceive('hasVersionMigrated')
            ->with($version2)
            ->andReturn(true)
            ->once();

        $version2->shouldReceive('markMigrated')
            ->never();

        $configuration->shouldReceive('getVersion')
            ->with($version3->getVersion())
            ->andReturn($version3)
            ->once();

        $configuration->shouldReceive('hasVersionMigrated')
            ->with($version3)
            ->andReturn(false)
            ->once();

        $version3->shouldReceive('markMigrated')
            ->once();

        $this->assertTrue($this->versionManager->markAllMigrated('patch', 'install', $config, MigrationsConstants::TYPE_DATABASE));
    }

    public function testMarkAllNotMigrated()
    {
        $config = array();

        $version1 = $this->createVersion('20160701000000');
        $version2 = $this->createVersion('20160702000000');
        $version3 = $this->createVersion('20160703000000');

        $configuration = Mockery::mock('Meteor\Migrations\Configuration\DatabaseConfiguration', array(
            'getAvailableVersions' => array(
                $version1->getVersion(),
                $version2->getVersion(),
                $version3->getVersion(),
            ),
        ));

        $this->configurationFactory->shouldReceive('createConfiguration')
            ->with(MigrationsConstants::TYPE_DATABASE, $config, 'patch', 'install')
            ->andReturn($configuration)
            ->once();

        $configuration->shouldReceive('getVersion')
            ->with($version1->getVersion())
            ->andReturn($version1)
            ->once();

        $configuration->shouldReceive('hasVersionMigrated')
            ->with($version1)
            ->andReturn(true)
            ->once();

        $version1->shouldReceive('markNotMigrated')
            ->once();

        $configuration->shouldReceive('getVersion')
            ->with($version2->getVersion())
            ->andReturn($version2)
            ->once();

        $configuration->shouldReceive('hasVersionMigrated')
            ->with($version2)
            ->andReturn(false)
            ->once();

        $version2->shouldReceive('markNotMigrated')
            ->never();

        $configuration->shouldReceive('getVersion')
            ->with($version3->getVersion())
            ->andReturn($version3)
            ->once();

        $configuration->shouldReceive('hasVersionMigrated')
            ->with($version3)
            ->andReturn(true)
            ->once();

        $version3->shouldReceive('markNotMigrated')
            ->once();

        $this->assertTrue($this->versionManager->markAllNotMigrated('patch', 'install', $config, MigrationsConstants::TYPE_DATABASE));
    }
}
