<?php

namespace Meteor\Package\Combined;

use Meteor\Configuration\ConfigurationLoader;
use Meteor\Filesystem\Filesystem;
use Meteor\IO\NullIO;
use Mockery;
use org\bovigo\vfs\vfsStream;

class PackageCombinerTest extends \PHPUnit_Framework_TestCase
{
    private $configurationLoader;
    private $filesystem;
    private $packageExtractor;
    private $migrationsCopier;
    private $io;
    private $packageCombiner;

    public function setUp()
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
        $packageConfig = array(
            'name' => 'jadu/xfp',
        );

        $outputDir = 'output';
        $tempDir = '/tmp/working';
        $extractedDir = '/tmp/extracted';
        $config = array(
            'name' => 'client',
        );

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
            ->with($extractedDir.'/to_patch', $tempDir.'/to_patch', array('/**'))
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

        $this->assertEquals(array(
            'name' => 'client',
            'combined' => array(
                array(
                    'name' => 'jadu/xfp',
                ),
            ),
        ), $updatedConfig);
    }

    public function testCombineExcludesVendor()
    {
        $packagePath = '/path/to/package.zip';
        $packageConfig = array(
            'name' => 'jadu/xfp',
        );

        $outputDir = 'output';
        $tempDir = '/tmp/working';
        $extractedDir = '/tmp/extracted';
        $config = array(
            'name' => 'client',
        );

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
            ->with($extractedDir.'/to_patch', $tempDir.'/to_patch', array('/**', '!/vendor'))
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

        $this->assertEquals(array(
            'name' => 'client',
            'combined' => array(
                array(
                    'name' => 'jadu/xfp',
                ),
            ),
        ), $updatedConfig);
    }

    public function testCombineHoistsCombinedPackages()
    {
        $combinedPackageConfig = array(
            'name' => 'jadu/cms',
        );

        $packagePath = '/path/to/package.zip';
        $packageConfig = array(
            'name' => 'jadu/xfp',
            'combined' => array($combinedPackageConfig),
        );

        $outputDir = 'output';
        $tempDir = '/tmp/working';
        $extractedDir = '/tmp/extracted';
        $config = array(
            'name' => 'client',
        );

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
            ->with($extractedDir, $tempDir, $combinedPackageConfig)
            ->andReturn($combinedPackageConfig)
            ->ordered()
            ->once();

        $this->filesystem->shouldReceive('remove')
            ->with($extractedDir)
            ->ordered()
            ->once();

        $updatedConfig = $this->packageCombiner->combine($packagePath, $outputDir, $tempDir, $config, false);

        $this->assertEquals(array(
            'name' => 'client',
            'combined' => array(
                array(
                    'name' => 'jadu/cms',
                ),
                array(
                    'name' => 'jadu/xfp',
                ),
            ),
        ), $updatedConfig);
    }

    public function testCombineDoesNotRemovePackageIfAlreadyExtracted()
    {
        vfsStream::setup('root');

        $packagePath = vfsStream::url('root');
        $packageConfig = array(
            'name' => 'jadu/xfp',
        );

        $outputDir = 'output';
        $tempDir = '/tmp/working';
        $config = array(
            'name' => 'client',
        );

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
            ->with($packagePath.'/to_patch', $tempDir.'/to_patch', Mockery::any())
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

        $this->assertEquals(array(
            'name' => 'client',
            'combined' => array(
                array(
                    'name' => 'jadu/xfp',
                ),
            ),
        ), $updatedConfig);
    }
}
