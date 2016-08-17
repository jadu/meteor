<?php

namespace Meteor\Package\Combined;

use Meteor\Configuration\ConfigurationLoader;
use Meteor\Filesystem\Filesystem;
use Meteor\IO\IOInterface;
use Meteor\Package\Migrations\MigrationsCopier;
use Meteor\Package\PackageConstants;
use Meteor\Package\PackageExtractor;

class PackageCombiner
{
    /**
     * @var ConfigurationLoader
     */
    private $configurationLoader;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var PackageExtractor
     */
    private $packageExtractor;

    /**
     * @var MigrationsCopier
     */
    private $migrationsCopier;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param ConfigurationLoader $configurationLoader
     * @param Filesystem $filesystem
     * @param PackageExtractor $packageExtractor
     * @param MigrationsCopier $migrationsCopier
     * @param IOInterface $io
     */
    public function __construct(
        ConfigurationLoader $configurationLoader,
        Filesystem $filesystem,
        PackageExtractor $packageExtractor,
        MigrationsCopier $migrationsCopier,
        IOInterface $io
    ) {
        $this->configurationLoader = $configurationLoader;
        $this->filesystem = $filesystem;
        $this->packageExtractor = $packageExtractor;
        $this->migrationsCopier = $migrationsCopier;
        $this->io = $io;
    }

    /**
     * @param string $packagePath
     * @param string $outputDir
     * @param string $tempDir
     * @param array $config
     * @param bool $excludeVendor
     *
     * @return array
     */
    public function combine($packagePath, $outputDir, $tempDir, array $config, $excludeVendor)
    {
        $this->io->section(sprintf('Combining package <info>%s</>', $packagePath));

        $extractedDir = $this->extractPackage($packagePath, $outputDir);
        $packageConfig = $this->configurationLoader->load($extractedDir, false);

        $this->io->text(sprintf('Found package: <info>%s</>', $packageConfig['name']));
        $this->io->newLine();

        $this->io->text('Copying files into the working directory:');

        // Include everything in the patch
        $include = array('/**');

        // Except vendor if excluded
        if ($excludeVendor) {
            $include[] = '!/vendor';
        }

        $this->filesystem->copyDirectory(
            $extractedDir.'/'.PackageConstants::PATCH_DIR,
            $tempDir.'/'.PackageConstants::PATCH_DIR,
            $include
        );

        // Copy migrations into place and update configuration
        $packageConfig = $this->migrationsCopier->copy($extractedDir, $tempDir, $packageConfig);

        if (!isset($config['combined'])) {
            $config['combined'] = array();
        }

        if (isset($packageConfig['combined'])) {
            foreach ($packageConfig['combined'] as $combinedConfig) {
                // Copy migrations into place and update configuration
                $combinedConfig = $this->migrationsCopier->copy($extractedDir, $tempDir, $combinedConfig);
                $config['combined'][] = $combinedConfig;
            }

            unset($packageConfig['combined'], $packageConfig['extensions']);
        }

        $config['combined'][] = $packageConfig;

        // NB: Only remove if the package was extracted, it may be an already extracted package or checkout
        if ($extractedDir !== $packagePath) {
            $this->io->debug(sprintf('Removing extracted package <info>%s</>', $extractedDir));
            $this->filesystem->remove($extractedDir);
        }

        return $config;
    }

    /**
     * Extracts the ZIP package, or returns the path if it's actually a directory.
     *
     * @param string $path
     * @param string $outputDir
     *
     * @return string The extracted path
     */
    private function extractPackage($path, $outputDir)
    {
        if (is_dir($path)) {
            // Not a ZIP archive
            return $path;
        }

        $tempDir = $this->filesystem->createTempDirectory($outputDir);

        $this->io->debug(sprintf('Extracting package contents to "%s"', $tempDir));

        return $this->packageExtractor->extract($path, $tempDir);
    }
}
