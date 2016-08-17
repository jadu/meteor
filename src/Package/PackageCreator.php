<?php

namespace Meteor\Package;

use Exception;
use Meteor\Configuration\ConfigurationLoader;
use Meteor\Configuration\ConfigurationWriter;
use Meteor\Filesystem\Filesystem;
use Meteor\IO\IOInterface;
use Meteor\Package\Combined\CombinedPackageResolver;
use Meteor\Package\Combined\Exception\CombinedPackageDependenciesException;
use Meteor\Package\Composer\ComposerDependencyChecker;
use Meteor\Package\Composer\Exception\ComposerDependenciesException;
use Meteor\Package\Migrations\MigrationsCopier;

class PackageCreator
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var PackageArchiver
     */
    private $packageArchiver;

    /**
     * @var PackageNameResolver
     */
    private $packageNameResolver;

    /**
     * @var MigrationsCopier
     */
    private $migrationsCopier;

    /**
     * @var CombinedPackageResolver
     */
    private $combinedPackageResolver;

    /**
     * @var ComposerDependencyChecker
     */
    private $composerDependencyChecker;

    /**
     * @var ConfigurationWriter
     */
    private $configurationWriter;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param Filesystem $filesystem
     * @param PackageArchiver $packageArchiver
     * @param PackageNameResolver $packageNameResolver
     * @param MigrationsCopier $migrationsCopier,
     * @param CombinedPackageResolver $combinedPackageResolver
     * @param ComposerDependencyChecker $composerDependencyChecker
     * @param ConfigurationWriter $configurationWriter
     * @param IOInterface $io
     */
    public function __construct(
        Filesystem $filesystem,
        PackageArchiver $packageArchiver,
        PackageNameResolver $packageNameResolver,
        MigrationsCopier $migrationsCopier,
        CombinedPackageResolver $combinedPackageResolver,
        ComposerDependencyChecker $composerDependencyChecker,
        ConfigurationWriter $configurationWriter,
        IOInterface $io
    ) {
        $this->filesystem = $filesystem;
        $this->packageArchiver = $packageArchiver;
        $this->packageNameResolver = $packageNameResolver;
        $this->migrationsCopier = $migrationsCopier;
        $this->combinedPackageResolver = $combinedPackageResolver;
        $this->composerDependencyChecker = $composerDependencyChecker;
        $this->configurationWriter = $configurationWriter;
        $this->io = $io;
    }

    /**
     * @param string $workingDir
     * @param string $outputDir
     * @param string $fileName
     * @param array  $config
     * @param array  $combinePackages
     * @param string $pharPath
     *
     * @return bool
     *
     * @throws Exception
     * @throws \Meteor\Configuration\RuntimeException
     */
    public function create($workingDir, $outputDir, $fileName, array $config, array $combinePackages, $pharPath = null)
    {
        $this->io->title(sprintf('Creating package for <info>%s</>', $config['name']));

        try {
            // Initialise the filesystem
            $this->filesystem->ensureDirectoryExists($outputDir);
            $tempDir = $this->filesystem->createTempDirectory($outputDir);

            $this->io->text('Copying files into the working directory:');
            $filesFilter = isset($config['package']['files']) ? $config['package']['files'] : null;
            $this->filesystem->copyDirectory($workingDir, $tempDir.'/'.PackageConstants::PATCH_DIR, $filesFilter);

            // Copy migrations into place and update configuration
            $config = $this->migrationsCopier->copy($workingDir, $tempDir, $config);

            // Check if there are any Composer requirements
            $composerRequirements = $this->composerDependencyChecker->getRequirements($workingDir);
            $hasComposerRequirements = !empty($composerRequirements);

            // Combine packages passed and automatically resolve required packages using the provider
            $config = $this->combinedPackageResolver->resolve($combinePackages, $outputDir, $tempDir, $config, $hasComposerRequirements);

            if ($hasComposerRequirements) {
                // Check that any required Composer dependencies are in the lock file
                $this->composerDependencyChecker->check($workingDir, $config);
                $config = $this->composerDependencyChecker->addRequirements($composerRequirements, $config);
            }

            // Write out the configuration to the built config that will contain combined package configs
            $this->io->debug('Writing meteor.json.package config file');
            $this->configurationWriter->write($tempDir.'/'.ConfigurationLoader::PACKAGE_CONFIG_NAME, $config);

            // Add the INSTRUCTIONS.md file to the package
            $this->addInstructionsToPackage($tempDir);

            // When Meteor is run using the Phar then add the PHAR to the package
            $this->addPharToPackage($tempDir, $pharPath);

            // Resolve the filename from the input (if NULL or invalid a filename will be generated)
            $fileName = $this->packageNameResolver->resolve($fileName, $workingDir, $config);

            // Create the archive
            $outputFile = $outputDir.'/'.$fileName.'.zip';
            $this->io->debug(sprintf('Creating archive <info>%s</>', $outputFile));
            $this->packageArchiver->archive($tempDir, $outputFile, $fileName);

            $this->io->success(sprintf('Package created successfully', $outputFile));
            $this->io->text(sprintf('The package is located at: <info>%s</>', realpath($outputFile)));
            $this->io->newLine();
        } catch (CombinedPackageDependenciesException $exception) {
            $this->handleCombinedPackageDependenciesException($exception);
            $this->cleanUp($tempDir);

            return false;
        } catch (ComposerDependenciesException $exception) {
            $this->handleComposerDependenciesException($exception);
            $this->cleanUp($tempDir);

            return false;
        } catch (Exception $exception) {
            $this->cleanUp($tempDir);

            // Rethrow exception after cleaning up
            throw $exception;
        }

        $this->cleanUp($tempDir);

        return true;
    }

    /**
     * Clean up any temp files created during the packaging process.
     *
     * @param string $tempDir
     */
    private function cleanUp($tempDir)
    {
        $this->io->debug(sprintf('Removing temp directory <info>%s</>', $tempDir));
        $this->filesystem->remove($tempDir);
    }

    /**
     * Combined package dependency exception handling and dumping to IO.
     *
     * @param CombinedPackageDependenciesException $exception
     */
    private function handleCombinedPackageDependenciesException(CombinedPackageDependenciesException $exception)
    {
        $this->io->error('The package could not be created as required Meteor packages were missing');

        $problems = array();
        foreach ($exception->getProblems() as $problem) {
            $problems[] = (string) $problem;
        }

        if (!empty($problems)) {
            $this->io->text('The following Meteor packages could not be found with the correct version:');
            $this->io->listing($problems);
        }

        $this->io->note('Use the --combine option to specify the paths to the required packages or configure a package provider');
    }

    /**
     * Dependency exception handling and dumping to IO.
     *
     * @param ComposerDependenciesException $exception
     */
    private function handleComposerDependenciesException(ComposerDependenciesException $exception)
    {
        $this->io->error('The package could not be created as required Composer packages were missing');

        $problems = array();
        foreach ($exception->getProblems() as $problem) {
            $problems[] = (string) $problem;
        }

        if (!empty($problems)) {
            $this->io->text('The following Composer packages could not be found with the correct version:');
            $this->io->listing($problems);
        }

        $this->io->error($exception->getMessage());
    }

    /**
     * @param string $tempDir
     */
    private function addInstructionsToPackage($tempDir)
    {
        $this->filesystem->copy(realpath(__DIR__.'/../../res/INSTRUCTIONS.md'), $tempDir.'/INSTRUCTIONS.md', true);
    }

    /**
     * Adds the meteor.phar file that is currently being run into the package.
     *
     * @param string $tempDir
     */
    private function addPharToPackage($tempDir, $pharPath)
    {
        if ($pharPath === null) {
            $pharPath = $this->getPharPath();
        }

        if (empty($pharPath)) {
            return;
        }

        $this->io->debug('Adding Phar archive to the package');
        $this->filesystem->copy($pharPath, $tempDir.'/meteor.phar', true);
    }

    /**
     * Returns the path to the Phar file running Meteor, or an empty string if not a Phar.
     *
     * @return string
     */
    private function getPharPath()
    {
        return \Phar::running(false);
    }
}
