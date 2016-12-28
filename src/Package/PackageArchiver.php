<?php

namespace Meteor\Package;

use Meteor\Filesystem\Filesystem;
use Meteor\IO\IOInterface;
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

        $this->io->debug('Adding files to the archive');
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS));

        foreach ($files as $file) {
            // Prefix all paths with the package name
            $relativePath = $packageName . '/' . preg_replace('/^' . preg_quote($sourceDir . '/', '/') . '/', '', $file->getPathname());
            $zip->addFile($file->getPathname(), $relativePath);

            $this->io->debug(' > ' . $relativePath);
        }

        $zip->close();
    }
}
