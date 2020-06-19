<?php

namespace Meteor\Filesystem;

use Meteor\Filesystem\Finder\FinderFactory;
use Meteor\IO\IOInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem as BaseFilesystem;

class Filesystem extends BaseFilesystem
{
    /**
     * @var FinderFactory
     */
    private $finderFactory;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param FinderFactory $finderFactory
     * @param IOInterface $io
     */
    public function __construct(FinderFactory $finderFactory, IOInterface $io)
    {
        $this->finderFactory = $finderFactory;
        $this->io = $io;
    }

    /**
     * Creates a new temp directory with a unique name.
     *
     * @param string|null $tempDir The temp directory to use
     *
     * @return string The path to the new directory
     */
    public function createTempDirectory($tempDir = null)
    {
        if ($tempDir === null) {
            $tempDir = sys_get_temp_dir();
        }

        $path = $tempDir . '/' . uniqid('meteor_tmp_');
        $this->mkdir($path);

        return $path;
    }

    /**
     * Create the given directory if it doesn't exist already.
     *
     * @param string $path
     *
     * @throws RuntimeException When the path is a file
     */
    public function ensureDirectoryExists($path)
    {
        if (!is_dir($path)) {
            if (file_exists($path) || is_link($path)) {
                // Either the file exists or it is a broken symlink
                throw new RuntimeException(sprintf('The path "%s" exists and is not a directory.', $path));
            }

            $this->mkdir($path);
        }
    }

    /**
     * @param string $sourceDir
     * @param string $targetDir
     * @param array|null $filters
     *
     * @return bool
     */
    public function copyDirectory($sourceDir, $targetDir, array $filters = [])
    {
        return $this->copyFiles($this->findFiles($sourceDir, $filters), $sourceDir, $targetDir);
    }

    /**
     * @param array $files
     * @param string $sourceDir
     * @param string $targetDir
     *
     * @return bool
     */
    public function copyFiles(array $files, $sourceDir, $targetDir)
    {
        $fileCount = count($files);
        if ($fileCount === 0) {
            $this->io->debug(sprintf('No files to copy from <info>%s</> to <info>%s</>', $sourceDir, $targetDir));

            return false;
        }

        $this->io->debug(sprintf('Copying %d files from <info>%s</> to <info>%s</>', $fileCount, $sourceDir, $targetDir));

        // Only display the progress bar when the output is not in debug mode
        $this->io->progressStart($fileCount);

        foreach ($files as $file) {
            $sourcePathname = $sourceDir . '/' . $file;
            $targetPathname = $targetDir . '/' . $file;

            if (is_dir($sourcePathname)) {
                $this->ensureDirectoryExists($targetPathname);
            } elseif (file_exists($sourcePathname)) {
                $this->ensureDirectoryExists(dirname($targetPathname));
                $this->copy($sourcePathname, $targetPathname, true);
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        return true;
    }

    /**
     * @param string $sourceDir
     * @param array $filters
     * @param bool $relative
     * @param int $depth
     *
     * @return array
     */
    public function findFiles($sourceDir, array $filters = [], $relative = true, $depth = null)
    {
        $files = [];

        // Normalize dir
        $sourceDir = realpath($sourceDir);

        if ($sourceDir === false) {
            return $files;
        }

        $finder = $this->finderFactory->create($sourceDir, $filters, $depth);
        foreach ($finder as $file) {
            $pathname = $file->getPathname();
            $pathname = realpath($pathname);
            if ($relative) {
                $pathname = $this->getRelativePath($sourceDir, $pathname);
            }

            $files[] = $pathname;
        }

        return $files;
    }

    /**
     * Returns files that do not exist in the target directory.
     *
     * @param string $baseDir
     * @param string $targetDir
     * @param array $filters
     *
     * @return array
     */
    public function findNewFiles($baseDir, $targetDir, array $filters = [])
    {
        $files = $this->findFiles($baseDir, $filters);

        return array_values(array_filter($files, function ($file) use ($targetDir) {
            return !file_exists($targetDir . '/' . $file);
        }));
    }

    /**
     * Returns the relative path.
     *
     * @param string $baseDir
     * @param string $path
     */
    public function getRelativePath($baseDir, $path)
    {
        // Add a directory separator as a suffix to ensure a full match
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return preg_replace('/^' . preg_quote($baseDir, '/') . '/', '', $path);
    }

    /**
     * Remove the directory and all of its contents
     *
     * @param string $directory
     */
    public function removeDirectory($directory)
    {
        if (file_exists($directory) && is_dir($directory)) {
            $objects = scandir($directory);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (is_dir($directory . '/' . $object)) {
                        $this->removeDirectory($directory . '/' . $object);
                    } else {
                        unlink($directory . '/' . $object);
                    }
                }
            }

            rmdir($directory);
        }
    }

    /**
     * Copy $replaceDirectory from $sourceDir to a temporary location, then remove $replaceDirectory from $targetDir and
     * replace it with the temporary copy. Used to quickly move a directory of items into place, completely removing
     * anything in the existing target location.
     *
     * Example usage: replaceDirectory('/package', '/install/home', '/vendor')
     *
     * @param string $sourceDir
     * @param string $targetDir
     * @param string $replaceDirectory
     */
    public function replaceDirectory($sourceDir, $targetDir, $replaceDirectory)
    {
        $this->io->text(sprintf('Replacing directory <info>%s</>', $targetDir . $replaceDirectory));

        $tempTarget = uniqid($replaceDirectory);

        $this->copyDirectory($sourceDir . $replaceDirectory, $targetDir . $tempTarget);

        $this->io->debug(sprintf("Removing %s", $targetDir . $replaceDirectory));
        $this->removeDirectory($targetDir . $replaceDirectory);

        $this->io->debug(sprintf("Renaming %s to %s", $targetDir . $tempTarget, $targetDir . $replaceDirectory));
        rename($targetDir . $tempTarget, $targetDir . $replaceDirectory);
    }
}
