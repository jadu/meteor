<?php

namespace Meteor\Package\Cli\Command;

use Meteor\Cli\Command\CommandTestCase;
use Meteor\IO\NullIO;
use Mockery;

class PackageCommandTest extends CommandTestCase
{
    private $packageCreator;

    public function createCommand()
    {
        $this->packageCreator = Mockery::mock('Meteor\Package\PackageCreator');

        return new PackageCommand(null, ['name' => 'test'], new NullIO(), $this->packageCreator);
    }

    public function testCreatesPackage()
    {
        $workingDir = __DIR__;
        $outputDir = __DIR__;

        $this->packageCreator->shouldReceive('create')
            ->with(
                $workingDir,
                $outputDir,
                'package.zip',
                ['name' => 'test'],
                [
                    '/path/to/package1.zip',
                    '/path/to/package2.zip',
                ],
                false,
                null
            )
            ->once();

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--output-dir' => $outputDir,
            '--filename' => 'package.zip',
            '--combine' => [
                '/path/to/package1.zip',
                '/path/to/package2.zip',
            ],
        ]);
    }

    public function testCreatesPackageWithoutCombiningPackages()
    {
        $workingDir = __DIR__;
        $outputDir = __DIR__;

        $this->packageCreator->shouldReceive('create')
            ->with(
                $workingDir,
                $outputDir,
                'package.zip',
                ['name' => 'test'],
                [],
                true,
                null
            )
            ->once();

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--output-dir' => $outputDir,
            '--filename' => 'package.zip',
            '--skip-combine' => true,
        ]);
    }

    public function testCreatesPackageWithPhar()
    {
        $workingDir = __DIR__;
        $outputDir = __DIR__;

        $this->packageCreator->shouldReceive('create')
            ->with(
                $workingDir,
                $outputDir,
                'package.zip',
                ['name' => 'test'],
                [],
                false,
                '/path/to/meteor.phar'
            )
            ->once();

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--output-dir' => $outputDir,
            '--filename' => 'package.zip',
            '--phar' => '/path/to/meteor.phar',
        ]);
    }
}
