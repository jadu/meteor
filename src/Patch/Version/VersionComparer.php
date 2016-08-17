<?php

namespace Meteor\Patch\Version;

use RuntimeException;

class VersionComparer
{
    /**
     * @param string $patchDir
     * @param string $installDir
     * @param string $packageName
     * @param string $versionFile
     *
     * @return VersionDiff
     */
    public function compare($patchDir, $installDir, $packageName, $versionFile)
    {
        $patchDir = rtrim($patchDir, '/');
        $patchVersionFile = $patchDir.'/'.$versionFile;
        if (!file_exists($patchVersionFile)) {
            throw new RuntimeException(sprintf('Unable to find version file "%s"', $patchVersionFile));
        }

        $patchVersion = $this->readVersion($patchVersionFile);

        $installDir = rtrim($installDir, '/');
        $installVersionFile = $installDir.'/'.$versionFile;
        if (file_exists($installVersionFile)) {
            $installVersion = $this->readVersion($installVersionFile);
        } else {
            $installVersion = null;
        }

        return new VersionDiff($packageName, $versionFile, $patchVersion, $installVersion);
    }

    /**
     * @param string $patchDir
     * @param string $installDir
     * @param array $config
     *
     * @return VersionDiff[]
     */
    public function comparePackage($patchDir, $installDir, array $config)
    {
        $versions = array();

        if (isset($config['package']['version'])) {
            try {
                $versions[] = $this->compare(
                    $patchDir,
                    $installDir,
                    $config['name'],
                    $config['package']['version']
                );
            } catch (RuntimeException $exception) {
                // Invalid backup
            }
        }

        if (isset($config['combined'])) {
            foreach ($config['combined'] as $combinedConfig) {
                if (isset($combinedConfig['package']['version'])) {
                    try {
                        $versions[] = $this->compare(
                            $patchDir,
                            $installDir,
                            $combinedConfig['name'],
                            $combinedConfig['package']['version']
                        );
                    } catch (RuntimeException $exception) {
                        // Invalid backup
                    }
                }
            }
        }

        return $versions;
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function readVersion($file)
    {
        return trim(file_get_contents($file));
    }
}
