<?php

namespace Meteor\Package;

use Meteor\Filesystem\Filesystem;
use Meteor\IO\NullIO;
use Meteor\Package\Composer\ComposerRequirement;
use Mockery;

class PackageCreatorTest extends \PHPUnit_Framework_TestCase
{
    private $filesystem;
    private $packageArchiver;
    private $packageCombiner;
    private $packageNameResolver;
    private $migrationsCopier;
    private $composerDependencyChecker;
    private $configurationWriter;
    private $io;
    private $packageCreator;

    public function setUp()
    {
        $this->filesystem = Mockery::mock('Meteor\Filesystem\Filesystem');
        $this->packageArchiver = Mockery::mock('Meteor\Package\PackageArchiver');
        $this->packageNameResolver = Mockery::mock('Meteor\Package\PackageNameResolver', [
            'resolve' => 'package',
        ]);
        $this->migrationsCopier = Mockery::mock('Meteor\Package\Migrations\MigrationsCopier');
        $this->combinedPackageResolver = Mockery::mock('Meteor\Package\Combined\CombinedPackageResolver');
        $this->composerDependencyChecker = Mockery::mock('Meteor\Package\Composer\ComposerDependencyChecker', [
            'getRequirements' => [],
        ]);
        $this->configurationWriter = Mockery::mock('Meteor\Configuration\ConfigurationWriter');
        $this->io = new NullIO();

        $this->packageCreator = new PackageCreator(
            $this->filesystem,
            $this->packageArchiver,
            $this->packageNameResolver,
            $this->migrationsCopier,
            $this->combinedPackageResolver,
            $this->composerDependencyChecker,
            $this->configurationWriter,
            $this->io
        );
    }

    public function testCombinePackagesWithNoPackageFiles()
    {
        $workingDir = 'working';
        $outputDir = 'output';
        $tempDir = 'temp';

        $config = [
            'name' => 'jadu/xfp',
            'package' => [
                'combine' => [],
            ],
        ];

        $this->filesystem->shouldReceive('ensureDirectoryExists')
            ->andReturn($outputDir)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('createTempDirectory')
            ->with($outputDir)
            ->andReturn($tempDir)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('copyDirectory')
            ->with($workingDir, $tempDir.'/to_patch', null)
            ->ordered()
            ->once();

        $this->migrationsCopier->shouldReceive('copy')
            ->with($workingDir, $tempDir, $config)
            ->andReturn($config)
            ->ordered()
            ->once();

        $this->combinedPackageResolver->shouldReceive('resolve')
            ->with(['cms.zip'], $outputDir, $tempDir, $config, false)
            ->andReturn($config)
            ->ordered()
            ->once();

        $this->configurationWriter->shouldReceive('write')
            ->with($tempDir.'/meteor.json.package', $config)
            ->ordered()
            ->once();

        $this->packageArchiver->shouldReceive('archive')
            ->with($tempDir, $outputDir.'/package.zip', 'package')
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with($tempDir)
            ->ordered()
            ->once();

        $this->packageCreator->create(
            $workingDir,
            $outputDir,
            'package',
            $config,
            ['cms.zip']
        );
    }

    public function testCanSkipCombiningPackages()
    {
        $workingDir = 'working';
        $outputDir = 'output';
        $tempDir = 'temp';

        $config = [
            'name' => 'jadu/xfp',
            'package' => [
                'combine' => [],
            ],
        ];

        $this->filesystem->shouldReceive('ensureDirectoryExists')
            ->andReturn($outputDir)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('createTempDirectory')
            ->with($outputDir)
            ->andReturn($tempDir)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('copyDirectory')
            ->with($workingDir, $tempDir.'/to_patch', null)
            ->ordered()
            ->once();

        $this->migrationsCopier->shouldReceive('copy')
            ->with($workingDir, $tempDir, $config)
            ->andReturn($config)
            ->ordered()
            ->once();

        $this->combinedPackageResolver->shouldReceive('resolve')
            ->never();

        $this->configurationWriter->shouldReceive('write')
            ->with($tempDir.'/meteor.json.package', $config)
            ->ordered()
            ->once();

        $this->packageArchiver->shouldReceive('archive')
            ->with($tempDir, $outputDir.'/package.zip', 'package')
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with($tempDir)
            ->ordered()
            ->once();

        $this->packageCreator->create(
            $workingDir,
            $outputDir,
            'package',
            $config,
            ['cms.zip'],
            true
        );
    }

    public function testCreate()
    {
        $workingDir = 'working';
        $outputDir = 'output';
        $tempDir = 'temp';

        $config = [
            'name' => 'jadu/xfp',
            'package' => [
                'files' => ['/**'],
                'combine' => [],
            ],
        ];

        $this->filesystem->shouldReceive('ensureDirectoryExists')
            ->andReturn($outputDir)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('createTempDirectory')
            ->with($outputDir)
            ->andReturn($tempDir)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('copyDirectory')
            ->with($workingDir, $tempDir.'/to_patch', ['/**'])
            ->ordered()
            ->once();

        $this->migrationsCopier->shouldReceive('copy')
            ->with($workingDir, $tempDir, $config)
            ->andReturn($config)
            ->ordered()
            ->once();

        $composerRequirements = [
            new ComposerRequirement('jadu/cms-dependencies', '~13.6.0'),
        ];

        $this->composerDependencyChecker->shouldReceive('getRequirements')
            ->with($workingDir)
            ->andReturn($composerRequirements)
            ->once();

        $this->combinedPackageResolver->shouldReceive('resolve')
            ->with(['cms.zip'], $outputDir, $tempDir, $config, true)
            ->andReturn($config)
            ->ordered()
            ->once();

        $this->composerDependencyChecker->shouldReceive('check')
            ->with($workingDir, $config)
            ->ordered()
            ->once();

        $this->composerDependencyChecker->shouldReceive('addRequirements')
            ->with($composerRequirements, $config)
            ->andReturn($config)
            ->ordered()
            ->once();

        $this->configurationWriter->shouldReceive('write')
            ->with($tempDir.'/meteor.json.package', $config)
            ->ordered()
            ->once();

        $this->packageArchiver->shouldReceive('archive')
            ->with($tempDir, $outputDir.'/package.zip', 'package')
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with($tempDir)
            ->ordered()
            ->once();

        $this->packageCreator->create(
            $workingDir,
            $outputDir,
            'package',
            $config,
            ['cms.zip']
        );
    }

    public function testCreateWithPharArchive()
    {
        $workingDir = 'working';
        $outputDir = 'output';
        $tempDir = 'temp';

        $config = [
            'name' => 'jadu/xfp',
            'package' => [
                'files' => ['/**'],
                'combine' => [],
            ],
        ];

        $this->filesystem->shouldReceive('ensureDirectoryExists')
            ->andReturn($outputDir)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('createTempDirectory')
            ->with($outputDir)
            ->andReturn($tempDir)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('copyDirectory')
            ->with($workingDir, $tempDir.'/to_patch', ['/**'])
            ->ordered()
            ->once();

        $this->migrationsCopier->shouldReceive('copy')
            ->with($workingDir, $tempDir, $config)
            ->andReturn($config)
            ->ordered()
            ->once();

        $composerRequirements = [];
        $this->composerDependencyChecker->shouldReceive('getRequirements')
            ->with($workingDir)
            ->andReturn($composerRequirements)
            ->once();

        $this->combinedPackageResolver->shouldReceive('resolve')
            ->with(['cms.zip'], $outputDir, $tempDir, $config, false)
            ->andReturn($config)
            ->ordered()
            ->once();

        $this->configurationWriter->shouldReceive('write')
            ->with($tempDir.'/meteor.json.package', $config)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('copy')
            ->with('meteor.phar', $tempDir.'/meteor.phar', true)
            ->ordered()
            ->once();

        $this->packageArchiver->shouldReceive('archive')
            ->with($tempDir, $outputDir.'/package.zip', 'package')
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with($tempDir)
            ->ordered()
            ->once();

        $this->packageCreator->create(
            $workingDir,
            $outputDir,
            'package',
            $config,
            ['cms.zip'],
            false,
            'meteor.phar'
        );
    }
}
