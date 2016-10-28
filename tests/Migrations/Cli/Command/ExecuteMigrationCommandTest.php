<?php

namespace Meteor\Migrations\Cli\Command;

use Meteor\IO\NullIO;
use Meteor\Logger\NullLogger;
use Meteor\Migrations\MigrationsConstants;
use Mockery;
use org\bovigo\vfs\vfsStream;

class ExecuteMigrationCommandTest extends MigrationTestCase
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

        return new ExecuteMigrationCommand('migrations:execute', [], new NullIO(), $this->platform, $this->migrator, new NullLogger(), MigrationsConstants::TYPE_DATABASE);
    }

    public function testExecuteMigrationUp()
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
        ];

        $this->command->setConfiguration(
            $this->extension->configParse($config)
        );

        $this->migrator->shouldReceive('execute')
            ->with($workingDir, $installDir, $config['migrations'], MigrationsConstants::TYPE_DATABASE, '20160701102030', 'up')
            ->andReturn(true)
            ->once();

        $this->tester->execute([
            'package' => 'test',
            'version' => '20160701102030',
            '--up' => null,
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ]);

        $this->assertSame(0, $this->tester->getStatusCode());
    }

    public function testExecuteMigrationDown()
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
        ];

        $this->command->setConfiguration(
            $this->extension->configParse($config)
        );

        $this->migrator->shouldReceive('execute')
            ->with($workingDir, $installDir, $config['migrations'], MigrationsConstants::TYPE_DATABASE, '20160701102030', 'down')
            ->andReturn(true)
            ->once();

        $this->tester->execute([
            'package' => 'test',
            'version' => '20160701102030',
            '--down' => null,
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ]);

        $this->assertSame(0, $this->tester->getStatusCode());
    }

    public function testRequiresPackageName()
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
        ];

        $this->command->setConfiguration(
            $this->extension->configParse($config)
        );

        $this->migrator->shouldReceive('execute')
            ->never();

        $this->tester->execute([
            'package' => '',
            'version' => '20160701102030',
            '--down' => null,
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ]);

        $this->assertSame(1, $this->tester->getStatusCode());
    }
}
