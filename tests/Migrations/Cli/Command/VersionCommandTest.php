<?php

namespace Meteor\Migrations\Cli\Command;

use Meteor\IO\NullIO;
use Meteor\Migrations\MigrationsConstants;
use Mockery;
use org\bovigo\vfs\vfsStream;

class VersionCommandTest extends MigrationTestCase
{
    private $platform;
    private $versionManager;

    public function createCommand()
    {
        $this->platform = Mockery::mock('Meteor\Platform\PlatformInterface');
        $this->versionManager = Mockery::mock('Meteor\Migrations\Version\VersionManager');

        vfsStream::setup('root', null, array(
            'working' => array(),
            'install' => array(),
        ));

        return new VersionCommand('migrations:version', array(), new NullIO(), $this->platform, $this->versionManager, MigrationsConstants::TYPE_DATABASE);
    }

    public function testMarkMigrated()
    {
        $workingDir = vfsStream::url('root/working');
        $installDir = vfsStream::url('root/install');

        $config = array(
            'name' => 'test',
            'migrations' => array(
                'name' => 'test',
                'table' => 'JaduMigrationsXFP',
                'namespace' => 'Migrations',
                'directory' => 'upgrades',
            ),
        );

        $this->command->setConfiguration(
            $this->extension->configParse($config)
        );

        $this->versionManager->shouldReceive('markMigrated')
            ->with($workingDir, $installDir, $config['migrations'], MigrationsConstants::TYPE_DATABASE, '20160701102030')
            ->andReturn(true)
            ->once();

        $this->tester->execute(array(
            'package' => 'test',
            'version' => '20160701102030',
            '--add' => null,
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ));
    }

    public function testMarkNotMigrated()
    {
        $workingDir = vfsStream::url('root/working');
        $installDir = vfsStream::url('root/install');

        $config = array(
            'name' => 'test',
            'migrations' => array(
                'name' => 'test',
                'table' => 'JaduMigrationsXFP',
                'namespace' => 'Migrations',
                'directory' => 'upgrades',
            ),
        );

        $this->command->setConfiguration(
            $this->extension->configParse($config)
        );

        $this->versionManager->shouldReceive('markNotMigrated')
            ->with($workingDir, $installDir, $config['migrations'], MigrationsConstants::TYPE_DATABASE, '20160701102030')
            ->andReturn(true)
            ->once();

        $this->tester->execute(array(
            'package' => 'test',
            'version' => '20160701102030',
            '--delete' => null,
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ));
    }

    public function testMarkAllMigrated()
    {
        $workingDir = vfsStream::url('root/working');
        $installDir = vfsStream::url('root/install');

        $config = array(
            'name' => 'test',
            'migrations' => array(
                'name' => 'test',
                'table' => 'JaduMigrationsXFP',
                'namespace' => 'Migrations',
                'directory' => 'upgrades',
            ),
        );

        $this->command->setConfiguration(
            $this->extension->configParse($config)
        );

        $this->versionManager->shouldReceive('markAllMigrated')
            ->with($workingDir, $installDir, $config['migrations'], MigrationsConstants::TYPE_DATABASE)
            ->andReturn(true)
            ->once();

        $this->tester->execute(array(
            'package' => 'test',
            '--add' => null,
            '--all' => true,
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ));

        $this->assertSame(0, $this->tester->getStatusCode());
    }

    public function testMarkAllNotMigrated()
    {
        $workingDir = vfsStream::url('root/working');
        $installDir = vfsStream::url('root/install');

        $config = array(
            'name' => 'test',
            'migrations' => array(
                'name' => 'test',
                'table' => 'JaduMigrationsXFP',
                'namespace' => 'Migrations',
                'directory' => 'upgrades',
            ),
        );

        $this->command->setConfiguration(
            $this->extension->configParse($config)
        );

        $this->versionManager->shouldReceive('markAllNotMigrated')
            ->with($workingDir, $installDir, $config['migrations'], MigrationsConstants::TYPE_DATABASE)
            ->andReturn(true)
            ->once();

        $this->tester->execute(array(
            'package' => 'test',
            '--delete' => null,
            '--all' => true,
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ));

        $this->assertSame(0, $this->tester->getStatusCode());
    }

    public function testRequiresPackageName()
    {
        $workingDir = vfsStream::url('root/working');
        $installDir = vfsStream::url('root/install');

        $config = array(
            'name' => 'test',
            'migrations' => array(
                'name' => 'test',
                'table' => 'JaduMigrationsXFP',
                'namespace' => 'Migrations',
                'directory' => 'upgrades',
            ),
        );

        $this->command->setConfiguration(
            $this->extension->configParse($config)
        );

        $this->versionManager->shouldReceive('markAllMigrated')
            ->never();

        $this->tester->execute(array(
            '--add' => null,
            '--all' => true,
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ));

        $this->assertSame(1, $this->tester->getStatusCode());
    }
}
