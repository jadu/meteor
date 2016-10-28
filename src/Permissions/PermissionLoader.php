<?php

namespace Meteor\Permissions;

use Symfony\Component\Finder\Finder;

class PermissionLoader
{
    const PERMISSIONS_CONFIG_DIR = '/config/permissions';

    /**
     * Load files permissions recursively from a given path.
     *
     * @param string $installDir
     *
     * @return array
     */
    public function load($installDir)
    {
        $permissions = [];

        // Load all permissions files in the path
        $finder = new Finder();
        foreach ($finder->in($installDir.self::PERMISSIONS_CONFIG_DIR)->files() as $fileInfo) {
            $file = $fileInfo->openFile();
            while (!$file->eof()) {
                $line = trim($file->fgets());
                if ($line !== '' && preg_match('/^(.*)\s+\[([rwx]{1,4})\]$/i', $line, $matches)) {
                    $permissions[] = Permission::create($matches[1], str_split($matches[2]));
                }
            }

            // Close the file
            $file = null;
        }

        return $permissions;
    }
}
