<?php

namespace Meteor\Package;

use Meteor\Filesystem\Filesystem;
use Meteor\IO\IOInterface;
use Meteor\Patch\Manifest\ManifestChecker;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

class PackageArchiver
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param Filesystem $filesystem
     * @param IOInterface $io
     */
    public function __construct(Filesystem $filesystem, IOInterface $io)
    {
        $this->filesystem = $filesystem;
        $this->io = $io;
    }

    /**
     * @param string $sourceDir
     * @param string $targetFile
     * @param string $packageName
     */
    public function archive($sourceDir, $targetFile, $packageName)
    {
        $zip = new ZipArchive();

        $sourceDir = rtrim($sourceDir, '/');

        if (file_exists($targetFile)) {
            // Remove the existing zip file
            $this->filesystem->remove($targetFile);
        }

        if ($zip->open($targetFile, ZipArchive::CREATE) !== true) {
            throw new RuntimeException(sprintf('Unable to open ZIP archive "%s"', $targetFile));
        }

        $this->filesystem->ensureDirectoryExists(dirname($targetFile));

        $this->io->text('Archiving the package files');
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS));

        $hashes = [];

        foreach ($files as $file) {
            $relativePath = preg_replace('/^' . preg_quote($sourceDir . '/', '/') . '/', '', $file->getPathname());
            $hashes[$relativePath] = sha1_file($file->getPathname());

            // Prefix all paths with the package name
            $packagePath = $packageName . '/' . $relativePath;
            $zip->addFile($file->getPathname(), $packagePath);

            $this->io->debug(' > ' . $packagePath);
        }

        // Store the file hashes in the manifest file
        $manifestPath = $sourceDir . '/' . ManifestChecker::MANIFEST_FILENAME;
        file_put_contents($manifestPath, json_encode($hashes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $zip->addFile($manifestPath, $packageName . '/' . basename($manifestPath));

        $zip->close();
    }
}
