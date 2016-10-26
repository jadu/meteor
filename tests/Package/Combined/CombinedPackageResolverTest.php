<?php

namespace Meteor\Package\Combined;

use Meteor\Filesystem\Filesystem;
use Meteor\IO\NullIO;
use Mockery;

class CombinedPackageResolverTest extends \PHPUnit_Framework_TestCase
{
    private $packageCombiner;
    private $combinedPackageDependencyChecker;
    private $filesystem;
    private $packageProvider;

    public function setUp()
    {
        $this->packageCombiner = Mockery::mock('Meteor\Package\Combined\PackageCombiner');
        $this->combinedPackageDependencyChecker = Mockery::mock('Meteor\Package\Combined\CombinedPackageDependencyChecker', [
            'check' => null,
        ]);
        $this->filesystem = Mockery::mock('Meteor\Filesystem\Filesystem');
        $this->packageProvider = Mockery::mock('Meteor\Package\Provider\PackageProviderInterface');

        $this->combinedPackageResolver = new CombinedPackageResolver(
            $this->packageCombiner,
            $this->combinedPackageDependencyChecker,
            $this->filesystem,
            new NullIO(),
            $this->packageProvider
        );
    }

    public function testResolveCombinesPackagesFromInput()
    {
        $outputDir = 'output';
        $tempDir = 'temp';

        $config = [
            'name' => 'test',
        ];

        $updatedConfig = $config;
        $updatedConfig['combined'] = [
            [
                'name' => 'jadu/cms',
            ],
        ];

        $this->packageCombiner->shouldReceive('combine')
            ->with('cms.zip', $outputDir, $tempDir, $config, true)
            ->andReturn($updatedConfig)
            ->once();

        $this->combinedPackageDependencyChecker->shouldReceive('check')
            ->with($tempDir, $updatedConfig)
            ->once();

        $this->assertSame($updatedConfig, $this->combinedPackageResolver->resolve(['cms.zip'], $outputDir, $tempDir, $config, true));
    }

    public function testResolveDoesNotDownloadPackageIfAlreadyCombined()
    {
        $outputDir = 'output';
        $tempDir = 'temp';

        $config = [
            'name' => 'test',
            'package' => [
                'combine' => [
                    'jadu/cms' => '13.6.0',
                ],
            ],
        ];

        $updatedConfig = $config;
        $updatedConfig['combined'] = [
            [
                'name' => 'jadu/cms',
            ],
        ];

        $this->packageCombiner->shouldReceive('combine')
            ->with('cms.zip', $outputDir, $tempDir, $config, true)
            ->andReturn($updatedConfig)
            ->once();

        $this->packageProvider->shouldReceive('download')
            ->never();

        $this->combinedPackageDependencyChecker->shouldReceive('check')
            ->with($tempDir, $updatedConfig)
            ->once();

        $this->assertSame($updatedConfig, $this->combinedPackageResolver->resolve(['cms.zip'], $outputDir, $tempDir, $config, true));
    }

    public function testResolveDoesNotDownloadPackageIfAlreadyCombinedViaAnotherCombinedPackage()
    {
        $outputDir = 'output';
        $tempDir = 'temp';

        $config = [
            'name' => 'test',
            'package' => [
                'combine' => [
                    'jadu/xfp' => '3.2.1',
                ],
            ],
        ];

        $updatedConfig = $config;
        $updatedConfig['combined'] = [
            [
                'name' => 'jadu/cms',
            ],
            [
                'name' => 'jadu/xfp',
            ],
        ];

        $this->packageCombiner->shouldReceive('combine')
            ->with('xfp.zip', $outputDir, $tempDir, $config, true)
            ->andReturn($updatedConfig)
            ->once();

        $this->packageProvider->shouldReceive('download')
            ->never();

        $this->combinedPackageDependencyChecker->shouldReceive('check')
            ->with($tempDir, $updatedConfig)
            ->once();

        $this->assertSame($updatedConfig, $this->combinedPackageResolver->resolve(['xfp.zip'], $outputDir, $tempDir, $config, true));
    }

    public function testResolveDownloadsPackages()
    {
        $outputDir = 'output';
        $tempDir = 'temp';

        $config = [
            'name' => 'test',
            'package' => [
                'combine' => [
                    'jadu/cms' => '13.6.0',
                ],
            ],
        ];

        $updatedConfig = $config;
        $updatedConfig['combined'] = [
            [
                'name' => 'jadu/cms',
            ],
        ];

        $this->filesystem->shouldReceive('createTempDirectory')
            ->with($outputDir)
            ->andReturn('download');

        $this->packageProvider->shouldReceive('download')
            ->with('jadu/cms', '13.6.0', 'download')
            ->andReturn('download/13.6.0.zip')
            ->once();

        $this->packageCombiner->shouldReceive('combine')
            ->with('download/13.6.0.zip', $outputDir, $tempDir, $config, true)
            ->andReturn($updatedConfig)
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with('download')
            ->once();

        $this->combinedPackageDependencyChecker->shouldReceive('check')
            ->with($tempDir, $updatedConfig)
            ->once();

        $this->assertSame($updatedConfig, $this->combinedPackageResolver->resolve([], $outputDir, $tempDir, $config, true));
    }

    public function testChecksPackageDependencies()
    {
        $outputDir = 'output';
        $tempDir = 'temp';

        $config = [
            'name' => 'test',
        ];

        $this->combinedPackageDependencyChecker->shouldReceive('check')
            ->with($tempDir, $config)
            ->once();

        $this->combinedPackageResolver->resolve([], $outputDir, $tempDir, $config, true);
    }
}
