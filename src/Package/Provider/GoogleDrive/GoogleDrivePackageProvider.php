<?php

namespace Meteor\Package\Provider\GoogleDrive;

use Meteor\IO\IOInterface;
use Meteor\Package\Provider\Exception\PackageNotFoundException;
use Meteor\Package\Provider\PackageProviderInterface;
use Meteor\Process\ProcessRunner;

class GoogleDrivePackageProvider implements PackageProviderInterface
{
    /**
     * @var ProcessRunner
     */
    private $processRunner;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var string
     */
    private $binaryPath;

    /**
     * @var array
     */
    private $packageFolders;

    /**
     * @param ProcessRunner $processRunner
     * @param IOInterface $io
     * @param string $binaryPath
     */
    public function __construct(ProcessRunner $processRunner, IOInterface $io, $binaryPath, array $packageFolders)
    {
        $this->processRunner = $processRunner;
        $this->io = $io;
        $this->binaryPath = $binaryPath;
        $this->packageFolders = $packageFolders;
    }

    /**
     * {@inheritdoc}
     */
    public function download($packageName, $version, $tempDir)
    {
        if (!isset($this->packageFolders[$packageName])) {
            throw new PackageNotFoundException(sprintf('The Google Drive folder for the "%s" package has not been configured.', $packageName));
        }

        $file = $version . '.zip';
        $folderId = $this->packageFolders[$packageName];

        $query = sprintf("name = '%s' and '%s' in parents", $file, $folderId);
        $this->processRunner->run(sprintf('%s download query %s --force', $this->binaryPath, escapeshellarg($query)), $tempDir);

        if (file_exists($tempDir . '/' . $file)) {
            return $tempDir . '/' . $file;
        }

        throw new PackageNotFoundException(sprintf('Unable to find "%s" in the Google Drive folder "%s".', $file, $folderId));
    }
}
