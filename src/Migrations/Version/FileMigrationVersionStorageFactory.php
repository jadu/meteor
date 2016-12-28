<?php

namespace Meteor\Migrations\Version;

use Meteor\Filesystem\Filesystem;

class FileMigrationVersionStorageFactory
{
    const STORAGE_DIR = '.meteor/file-migrations';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $installDir
     * @param string $table
     *
     * @return FileMigrationVersionStorage
     */
    public function create($installDir, $table)
    {
        $storageDir = $installDir . '/' . self::STORAGE_DIR;
        $this->filesystem->ensureDirectoryExists($storageDir);

        return new FileMigrationVersionStorage($storageDir . '/' . strtolower($table));
    }
}
