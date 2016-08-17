<?php

namespace Meteor\Migrations\Version;

class VersionFileManager
{
    const DATABASE_MIGRATION = 'MIGRATION_NUMBER';
    const FILE_MIGRATION = 'FILE_SYSTEM_MIGRATION_NUMBER';

    /**
     * @param string $path
     * @param string $table
     * @param string $versionFilename
     *
     * @return string
     */
    public function getCurrentVersion($path, $table, $versionFilename)
    {
        $versionFile = $this->getVersionFile($table, $versionFilename);
        if (file_exists($path.'/'.$versionFile)) {
            $currentVesion = trim(file_get_contents($path.'/'.$versionFile));
            if ($currentVesion !== '') {
                return $currentVesion;
            }
        }

        return '0';
    }

    /**
     * @param string $currentVersion
     * @param string $path
     * @param string $table
     * @param string $versionFilename
     */
    public function setCurrentVersion($currentVersion, $path, $table, $versionFilename)
    {
        $versionFile = $this->getVersionFile($table, $versionFilename);

        file_put_contents($path.'/'.$versionFile, $currentVersion);
    }

    /**
     * @param string $table
     * @param string $versionFilename
     *
     * @return string
     */
    private function getVersionFile($table, $versionFilename)
    {
        if (stripos($table, 'JaduMigrations') === 0) {
            $table = substr($table, strlen('JaduMigrations'));
            if (strlen($table) === 0) {
                return $versionFilename;
            }
        }

        return strtoupper($table).'_'.$versionFilename;
    }
}
