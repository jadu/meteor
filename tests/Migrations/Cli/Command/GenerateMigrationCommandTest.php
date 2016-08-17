<?php

namespace Meteor\Migrations\Cli\Command;

use Meteor\Cli\Command\CommandTestCase;
use Meteor\IO\NullIO;
use Meteor\Migrations\Generator\MigrationGenerator;
use Meteor\Migrations\MigrationsConstants;
use Mockery;

class GenerateMigrationCommandTest extends CommandTestCase
{
    private $migrationGenerator;

    public function createCommand()
    {
        $this->migrationGenerator = Mockery::mock('Meteor\Migrations\Generator\MigrationGenerator');

        return new GenerateMigrationCommand('migrations:generate', array(), new NullIO(), $this->migrationGenerator, MigrationsConstants::TYPE_DATABASE);
    }

    public function testGeneratesMigration()
    {
        $workingDir = __DIR__;

        $this->command->setConfiguration(array(
            'migrations' => array(
                'name' => 'jadu/xfp',
                'table' => 'JaduMigrationsXFP',
                'namespace' => 'Migrations',
                'directory' => 'upgrades',
            ),
        ));

        $this->migrationGenerator->shouldReceive('generate')
            ->with(Mockery::any(), 'Migrations', $workingDir.'/upgrades')
            ->once();

        $this->tester->execute(array(
            '--working-dir' => $workingDir,
        ));
    }
}
