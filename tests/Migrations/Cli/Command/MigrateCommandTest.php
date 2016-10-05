<?php

namespace Meteor\Migrations\Cli\Command;

use Meteor\IO\NullIO;
use Meteor\Logger\NullLogger;
use Meteor\Migrations\MigrationsConstants;
use Mockery;
use org\bovigo\vfs\vfsStream;

class MigrateCommandTest extends MigrationTestCase
{
    private $platform;
    private $migrator;

    public function createCommand()
    {
        $this->platform = Mockery::mock('Meteor\Platform\PlatformInterface');
        $this->migrator = Mockery::mock('Meteor\Migrations\Migrator');

        vfsStream::setup('root', null, array(
            'working' => array(),
            'install' => array(),
        ));

        return new MigrateCommand('migrations:migrate', array(), new NullIO(), $this->platform, $this->migrator, new NullLogger(), MigrationsConstants::TYPE_DATABASE);
    }

    public function testMigrateRunsMigrationsInOrder()
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
            'combined' => array(
                array(
                    'name' => 'test1',
                    'migrations' => array(
                        'table' => 'Migrations1',
                    ),
                ),
                array(
                    'name' => 'test2',
                    'migrations' => array(
                        'table' => 'Migrations2',
                    ),
                ),
            ),
        );

        $this->command->setConfiguration(
            $this->extension->configParse($config)
        );

        $this->migrator->shouldReceive('migrate')
            ->with($workingDir, $installDir, $config['combined'][0]['migrations'], MigrationsConstants::TYPE_DATABASE, 'latest')
            ->andReturn(true)
            ->ordered()
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with($workingDir, $installDir, $config['combined'][1]['migrations'], MigrationsConstants::TYPE_DATABASE, 'latest')
            ->andReturn(true)
            ->ordered()
            ->once();

        $this->migrator->shouldReceive('migrate')
            ->with($workingDir, $installDir, $config['migrations'], MigrationsConstants::TYPE_DATABASE, 'latest')
            ->andReturn(true)
            ->ordered()
            ->once();

        $this->tester->execute(array(
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ));
    }
}
