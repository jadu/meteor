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

        vfsStream::setup('root', null, [
            'working' => [],
            'install' => [],
        ]);

        return new MigrateCommand('migrations:migrate', [], new NullIO(), $this->platform, $this->migrator, new NullLogger(), MigrationsConstants::TYPE_DATABASE);
    }

    public function testMigrateRunsMigrationsInOrder()
    {
        $workingDir = vfsStream::url('root/working');
        $installDir = vfsStream::url('root/install');

        $config = [
            'name' => 'test',
            'migrations' => [
                'name' => 'test',
                'table' => 'JaduMigrationsXFP',
                'namespace' => 'Migrations',
                'directory' => 'upgrades',
            ],
            'combined' => [
                [
                    'name' => 'test1',
                    'migrations' => [
                        'table' => 'Migrations1',
                    ],
                ],
                [
                    'name' => 'test2',
                    'migrations' => [
                        'table' => 'Migrations2',
                    ],
                ],
            ],
        ];

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

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ]);
    }
}
