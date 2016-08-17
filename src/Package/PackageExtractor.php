<?php

namespace Meteor\Package;

use Meteor\Configuration\ConfigurationLoader;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

class PackageExtractor
{
    /**
     * @param string $packagePath
     * @param string $targetDir
     *
     * @throws RuntimeException
     *
     * @return string
     */
    public function extract($packagePath, $targetDir)
    {
        $zip = new ZipArchive();
        if ($zip->open($packagePath) !== true) {
            throw new RuntimeException(sprintf('Unable to open ZIP archive "%s"', $packagePath));
        }

        $zip->extractTo($targetDir);
        $zip->close();

        return $this->findFirstDirectoryWithMeteorConfig($targetDir);
    }

    /**
     * @param string $path
     *
     * @throws RuntimeException
     *
     * @return string
     */
    private function findFirstDirectoryWithMeteorConfig($path)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        foreach ($iterator as $file) {
            if (in_array($file->getFilename(), array(ConfigurationLoader::CONFIG_NAME, ConfigurationLoader::PACKAGE_CONFIG_NAME), true)) {
                return $file->getPath();
            }
        }

        throw new RuntimeException(sprintf('Unable to find %s config file in the extracted package', ConfigurationLoader::PACKAGE_CONFIG_NAME));
    }
}
