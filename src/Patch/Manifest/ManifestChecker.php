<?php

namespace Meteor\Patch\Manifest;

use Meteor\IO\IOInterface;

class ManifestChecker
{
    const MANIFEST_FILENAME = 'meteor.manifest';

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param IOInterface $io
     */
    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }

    /**
     * @param string $workingDir
     */
    public function check($workingDir)
    {
        $manifestFile = $workingDir . '/' . self::MANIFEST_FILENAME;
        if (!file_exists($manifestFile)) {
            // The manifest did not exist
            return false;
        }

        $manifest = json_decode(file_get_contents($manifestFile));
        if (!$manifest) {
            $this->io->error('The package manifest could not be read.');

            return;
        }

        $errors = [];
        foreach ($manifest as $path => $hash) {
            $workingPath = $workingDir . '/' . $path;
            if (!file_exists($workingPath)) {
                $errors[] = sprintf('"%s" does not exist', $path);
            }

            if (!hash_equals($hash, sha1_file($workingPath))) {
                $errors[] = sprintf('"%s" does not match the expected file contents', $path);
            }
        }

        if (!empty($errors)) {
            $this->io->error('The patch cannot be applied as the package could not be verified against the manifest.');
            $this->io->listing($errors);

            return false;
        }

        return true;
    }
}
