<?php

namespace Meteor\Package\Combined;

use Meteor\IO\NullIO;
use Mockery;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class PackageCombinerTest extends TestCase
{
    private $configurationLoader;
    private $filesystem;
    private $packageExtractor;
    private $migrationsCopier;
    private $io;
    private $packageCombiner;

    protected function setUp(): void
    {
        $this->configurationLoader = Mockery::mock('Meteor\Configuration\ConfigurationLoader');
        $this->filesystem = Mockery::mock('Meteor\Filesystem\Filesystem');
        $this->packageExtractor = Mockery::mock('Meteor\Package\PackageExtractor');
        $this->migrationsCopier = Mockery::mock('Meteor\Package\Migrations\MigrationsCopier');
        $this->io = new NullIO();

        $this->packageCombiner = new PackageCombiner(
            $this->configurationLoader,
            $this->filesystem,
            $this->packageExtractor,
            $this->migrationsCopier,
            $this->io
        );
    }

    public function testCombine()
    {
        $packagePath = '/path/to/package.zip';
        $packageConfig = [
            'name' => 'jadu/xfp',
        ];

        $outputDir = 'output';
        $tempDir = '/tmp/working';
        $extractedDir = '/tmp/extracted';
        $config = [
            'name' => 'client',
        ];

        $this->filesystem->shouldReceive('createTempDirectory')
            ->with($outputDir)
            ->andReturn($extractedDir)
            ->ordered()
            ->once();

        $this->packageExtractor->shouldReceive('extract')
            ->with($packagePath, $extractedDir)
            ->andReturn($extractedDir)
            ->ordered()
            ->once();

        $this->configurationLoader->shouldReceive('load')
            ->with($extractedDir, false)
            ->andReturn($packageConfig)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('copyDirectory')
            ->with($extractedDir . '/to_patch', $tempDir . '/to_patch', ['/**'])
            ->ordered()
            ->once();

        $this->migrationsCopier->shouldReceive('copy')
            ->with($extractedDir, $tempDir, $packageConfig)
            ->andReturn($packageConfig)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with($extractedDir)
            ->ordered()
            ->once();

        $updatedConfig = $this->packageCombiner->combine($packagePath, $outputDir, $tempDir, $config, false);

        static::assertEquals([
            'name' => 'client',
            'combined' => [
                [
                    'name' => 'jadu/xfp',
                ],
            ],
        ], $updatedConfig);
    }

    public function testCombineExcludesVendor()
    {
        $packagePath = '/path/to/package.zip';
        $packageConfig = [
            'name' => 'jadu/xfp',
        ];

        $outputDir = 'output';
        $tempDir = '/tmp/working';
        $extractedDir = '/tmp/extracted';
        $config = [
            'name' => 'client',
        ];

        $this->filesystem->shouldReceive('createTempDirectory')
            ->with($outputDir)
            ->andReturn($extractedDir)
            ->ordered()
            ->once();

        $this->packageExtractor->shouldReceive('extract')
            ->with($packagePath, $extractedDir)
            ->andReturn($extractedDir)
            ->ordered()
            ->once();

        $this->configurationLoader->shouldReceive('load')
            ->with($extractedDir, false)
            ->andReturn($packageConfig)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('copyDirectory')
            ->with($extractedDir . '/to_patch', $tempDir . '/to_patch', ['/**', '!/vendor'])
            ->ordered()
            ->once();

        $this->migrationsCopier->shouldReceive('copy')
            ->with($extractedDir, $tempDir, $packageConfig)
            ->andReturn($packageConfig)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with($extractedDir)
            ->ordered()
            ->once();

        $updatedConfig = $this->packageCombiner->combine($packagePath, $outputDir, $tempDir, $config, true);

        static::assertEquals([
            'name' => 'client',
            'combined' => [
                [
                    'name' => 'jadu/xfp',
                ],
            ],
        ], $updatedConfig);
    }

    public function testCombineHoistsCombinedPackages()
    {
        $packagePath = '/path/to/package.zip';
        $packageConfig = [
            'name' => 'jadu/xfp',
            'combined' => [
                [
                    'name' => 'jadu/cms',
                ],
            ],
        ];

        $outputDir = 'output';
        $tempDir = '/tmp/working';
        $extractedDir = '/tmp/extracted';
        $config = [
            'name' => 'client',
        ];

        $this->filesystem->shouldReceive('createTempDirectory')
            ->with($outputDir)
            ->andReturn($extractedDir)
            ->ordered()
            ->once();

        $this->packageExtractor->shouldReceive('extract')
            ->with($packagePath, $extractedDir)
            ->andReturn($extractedDir)
            ->ordered()
            ->once();

        $this->configurationLoader->shouldReceive('load')
            ->with($extractedDir, false)
            ->andReturn($packageConfig)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('copyDirectory')
            ->ordered()
            ->once();

        $this->migrationsCopier->shouldReceive('copy')
            ->with($extractedDir, $tempDir, $packageConfig)
            ->andReturn($packageConfig)
            ->ordered()
            ->once();

        $this->migrationsCopier->shouldReceive('copy')
            ->with($extractedDir, $tempDir, $packageConfig['combined'][0])
            ->andReturn($packageConfig['combined'][0])
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with($extractedDir)
            ->ordered()
            ->once();

        $updatedConfig = $this->packageCombiner->combine($packagePath, $outputDir, $tempDir, $config, false);

        static::assertEquals([
            'name' => 'client',
            'combined' => [
                [
                    'name' => 'jadu/cms',
                ],
                [
                    'name' => 'jadu/xfp',
                ],
            ],
        ], $updatedConfig);
    }

    public function testIgnoresAlreadyCombinedPackages()
    {
        $packagePath = '/path/to/package.zip';
        $packageConfig = [
            'name' => 'jadu/xfp',
            'combined' => [
                [
                    'name' => 'jadu/cms',
                ],
            ],
        ];

        $outputDir = 'output';
        $tempDir = '/tmp/working';
        $extractedDir = '/tmp/extracted';
        $config = [
            'name' => 'client',
            'combined' => [
                [
                    'name' => 'jadu/cms',
                ],
            ],
        ];

        $this->filesystem->shouldReceive('createTempDirectory')
            ->with($outputDir)
            ->andReturn($extractedDir)
            ->ordered()
            ->once();

        $this->packageExtractor->shouldReceive('extract')
            ->with($packagePath, $extractedDir)
            ->andReturn($extractedDir)
            ->ordered()
            ->once();

        $this->configurationLoader->shouldReceive('load')
            ->with($extractedDir, false)
            ->andReturn($packageConfig)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('copyDirectory')
            ->ordered()
            ->once();

        $this->migrationsCopier->shouldReceive('copy')
            ->with($extractedDir, $tempDir, $packageConfig)
            ->andReturn($packageConfig)
            ->ordered()
            ->once();

        $this->migrationsCopier->shouldReceive('copy')
            ->with($extractedDir, $tempDir, $packageConfig['combined'][0])
            ->andReturn($packageConfig['combined'][0])
            ->never();

        $this->filesystem->shouldReceive('remove')
            ->with($extractedDir)
            ->ordered()
            ->once();

        $updatedConfig = $this->packageCombiner->combine($packagePath, $outputDir, $tempDir, $config, false);

        static::assertEquals([
            'name' => 'client',
            'combined' => [
                [
                    'name' => 'jadu/cms',
                ],
                [
                    'name' => 'jadu/xfp',
                ],
            ],
        ], $updatedConfig);
    }

    public function testCombineDoesNotRemovePackageIfAlreadyExtracted()
    {
        vfsStream::setup('root');

        $packagePath = vfsStream::url('root');
        $packageConfig = [
            'name' => 'jadu/xfp',
        ];

        $outputDir = 'output';
        $tempDir = '/tmp/working';
        $config = [
            'name' => 'client',
        ];

        $this->filesystem->shouldReceive('createTempDirectory')
            ->never();

        $this->packageExtractor->shouldReceive('extract')
            ->never();

        $this->configurationLoader->shouldReceive('load')
            ->with($packagePath, false)
            ->andReturn($packageConfig)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('copyDirectory')
            ->with($packagePath . '/to_patch', $tempDir . '/to_patch', Mockery::any())
            ->ordered()
            ->once();

        $this->migrationsCopier->shouldReceive('copy')
            ->with($packagePath, $tempDir, $packageConfig)
            ->andReturn($packageConfig)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with($packagePath)
            ->never();

        $updatedConfig = $this->packageCombiner->combine($packagePath, $outputDir, $tempDir, $config, false);

        static::assertEquals([
            'name' => 'client',
            'combined' => [
                [
                    'name' => 'jadu/xfp',
                ],
            ],
        ], $updatedConfig);
    }
}
