<?php

namespace Meteor\Package\Migrations;

use Meteor\Filesystem\Filesystem;
use Meteor\IO\IOInterface;

class MigrationsCopier
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
     * @param string $workingDir
     * @param string $tempDir
     * @param array $config
     *
     * @return array
     */
    public function copy($workingDir, $tempDir, array $config)
    {
        if (isset($config['migrations'])) {
            $this->io->text('Adding migrations to the package:');
            $migrationDirectory = 'migrations/'.$config['name'];

            $this->filesystem->copyDirectory(
                $workingDir.'/'.$config['migrations']['directory'],
                $tempDir.'/'.$migrationDirectory
            );

            $config['migrations']['directory'] = $migrationDirectory;
        }

        return $config;
    }
}
