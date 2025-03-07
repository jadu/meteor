<?php

namespace Meteor\Package\Combined;

use Meteor\Filesystem\Filesystem;
use Meteor\IO\IOInterface;
use Meteor\Package\Provider\Exception\PackageNotFoundException;
use Meteor\Package\Provider\PackageProviderInterface;

class CombinedPackageResolver
{
    /**
     * @var PackageCombiner
     */
    private $packageCombiner;

    /**
     * @var CombinedPackageDependencyChecker
     */
    private $combinedPackageDependencyChecker;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var PackageProviderInterface
     */
    private $packageProvider;

    /**
     * @param PackageCombiner $packageCombiner
     * @param CombinedPackageDependencyChecker $combinedPackageDependencyChecker
     * @param Filesystem $filesystem
     * @param IOInterface $io
     * @param PackageProviderInterface $packageProvider
     */
    public function __construct(PackageCombiner $packageCombiner, CombinedPackageDependencyChecker $combinedPackageDependencyChecker, Filesystem $filesystem, IOInterface $io, ?PackageProviderInterface $packageProvider = null)
    {
        $this->packageCombiner = $packageCombiner;
        $this->combinedPackageDependencyChecker = $combinedPackageDependencyChecker;
        $this->filesystem = $filesystem;
        $this->io = $io;
        $this->packageProvider = $packageProvider;
    }

    /**
     * @param array $combinePackages
     * @param string $outputDir
     * @param string $tempDir
     * @param array $config
     * @param $excludeVendor
     *
     * @return array
     */
    public function resolve(array $combinePackages, $outputDir, $tempDir, array $config, $excludeVendor)
    {
        // Combine the packages passed with --combine first
        foreach ($combinePackages as $packagePath) {
            $config = $this->packageCombiner->combine($packagePath, $outputDir, $tempDir, $config, $excludeVendor);
        }

        $requiredPackages = $config['package']['combine'] ?? [];

        if (!empty($requiredPackages) && $this->packageProvider !== null) {
            foreach ($requiredPackages as $packageName => $version) {
                if (!$this->hasCombinedPackage($packageName, $config)) {
                    $downloadDir = $this->filesystem->createTempDirectory($outputDir);
                    $this->io->debug(sprintf('Finding package %s', $packageName));

                    try {
                        $packagePath = $this->packageProvider->download($packageName, $version, $downloadDir);
                        $this->io->text(sprintf('Downloaded package %s', $packageName));

                        $config = $this->packageCombiner->combine($packagePath, $outputDir, $tempDir, $config, $excludeVendor);
                    } catch (PackageNotFoundException $exception) {
                        // Output the exception message as an error
                        $this->io->text(sprintf('<fg=red>%s</>', $exception->getMessage()));
                    }

                    $this->filesystem->remove($downloadDir);
                }
            }
        }

        $this->combinedPackageDependencyChecker->check($tempDir, $config);

        return $config;
    }

    /**
     * @param string $packageName
     * @param array $config
     *
     * @return bool
     */
    private function hasCombinedPackage($packageName, array $config)
    {
        if (isset($config['combined'])) {
            foreach ($config['combined'] as $combinedConfig) {
                if ($combinedConfig['name'] === $packageName) {
                    return true;
                }
            }
        }

        return false;
    }
}
