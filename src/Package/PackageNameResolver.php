<?php

namespace Meteor\Package;

use RuntimeException;

class PackageNameResolver
{
    /**
     * @param string $fileName
     * @param string $workingDir
     * @param array $config
     *
     * @return string
     */
    public function resolve(string $fileName, string $workingDir, array $config)
    {
        $fileName = trim($fileName);
        if ($this->isValid($fileName)) {
            return $fileName;
        }

        return $this->generateFileName($workingDir, $config);
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    private function isValid($fileName)
    {
        return (bool) preg_match('/^[a-z0-9][a-z0-9_\-\.]*$/i', $fileName);
    }

    /**
     * @param array $config
     *
     * @return string
     */
    private function generateFileName($workingDir, array $config)
    {
        $fileName = preg_replace('/[^a-z0-9_\-\.]+/i', '_', $config['name']);

        if (isset($config['package']) && isset($config['package']['version'])) {
            $versionFile = $workingDir . '/' . $config['package']['version'];
            if (!file_exists($versionFile)) {
                throw new RuntimeException(sprintf('Unable to find version file "%s"', $versionFile));
            }

            $version = trim(file_get_contents($versionFile));
            $version = preg_replace('/[^a-z0-9_\-\.]+/i', '_', $version);
            $version = trim($version, '_');

            if ($version !== '') {
                $fileName .= '_' . $version;
            }
        }

        return $fileName;
    }
}
