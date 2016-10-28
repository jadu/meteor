<?php

namespace Meteor\Patch\Backup;

use DirectoryIterator;
use Meteor\Configuration\ConfigurationLoader;
use Meteor\Configuration\Exception\ConfigurationLoadingException;
use Meteor\Package\PackageConstants;
use Meteor\Patch\Version\VersionComparer;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class BackupFinder
{
    /**
     * @var VersionComparer
     */
    private $versionComparer;

    /**
     * @var ConfigurationLoader
     */
    private $configurationLoader;

    /**
     * @param VersionComparer $versionComparer
     * @param ConfigurationLoader $configurationLoader
     */
    public function __construct(VersionComparer $versionComparer, ConfigurationLoader $configurationLoader)
    {
        $this->versionComparer = $versionComparer;
        $this->configurationLoader = $configurationLoader;
    }

    /**
     * @param string $installDir
     * @param array $config
     *
     * @return Backup[]
     */
    public function find($installDir, array $config)
    {
        $backups = [];
        $backupsDir = $installDir.'/backups';
        $packageNames = $this->getPackageNames($config);

        $backupDirs = [];
        foreach (new DirectoryIterator($backupsDir) as $file) {
            if (!$file->isDot() && $file->isDir() && preg_match('/^\d{14}$/', $file->getFilename())) {
                $backupDirs[] = $file->getPathname();
            }
        }

        rsort($backupDirs);

        foreach ($backupDirs as $backupDir) {
            try {
                $backupConfig = $this->configurationLoader->load($backupDir);
                $backupPackageNames = $this->getPackageNames($backupConfig);

                // Check that the backup contains the same packages
                if ($packageNames === $backupPackageNames) {
                    $versions = $this->versionComparer->comparePackage($backupDir.'/'.PackageConstants::PATCH_DIR, $installDir, $config);
                    $backup = new Backup($backupDir, $versions);

                    if ($backup->isValid()) {
                        $backups[] = $backup;
                    }
                }
            } catch (ConfigurationLoadingException $exception) {
                // Not a valid backup
            } catch (InvalidConfigurationException $exception) {
                // Unable to parse config
            }
        }

        return $backups;
    }

    /**
     * @param array $config
     *
     * @return array
     */
    private function getPackageNames(array $config)
    {
        $packageNames = [$config['name']];

        if (isset($config['combined'])) {
            foreach ($config['combined'] as $combinedConfig) {
                $packageNames[] = $combinedConfig['name'];
            }
        }

        sort($packageNames);
        $packageNames = array_values($packageNames);

        return $packageNames;
    }
}
