<?php

namespace Meteor\Patch\Manifest;

use Meteor\IO\NullIO;
use org\bovigo\vfs\vfsStream;

class ManifestCheckerTest extends \PHPUnit_Framework_TestCase
{
    private $manifestChecker;

    public function setUp()
    {
        $this->manifestChecker = new ManifestChecker(new NullIO());

        vfsStream::setup('root', null, [
            'a.txt' => 'a',
            'b.txt' => 'b',
            'c.txt' => 'c',
        ]);
    }

    public function testReturnsFalseIfManifestFileDoesNotExist()
    {
        $this->assertFalse($this->manifestChecker->check(vfsStream::url('root')));
    }

    public function testReturnsFalseIfManifestFileCouldNotBeParsed()
    {
        file_put_contents(vfsStream::url('root/' . ManifestChecker::MANIFEST_FILENAME), '!!');

        $this->assertFalse($this->manifestChecker->check(vfsStream::url('root')));
    }

    public function testReturnsFalseIfThePatchCannotBeVerifiedDueToMissingFile()
    {
        $manifest = [
            'd.txt' => 'hash',
        ];

        file_put_contents(vfsStream::url('root/' . ManifestChecker::MANIFEST_FILENAME), json_encode($manifest));

        $this->assertFalse($this->manifestChecker->check(vfsStream::url('root')));
    }

    public function testReturnsFalseIfThePatchCannotBeVerifiedDueToInvalidFileHash()
    {
        $manifest = [
            'c.txt' => 'hash',
        ];

        file_put_contents(vfsStream::url('root/' . ManifestChecker::MANIFEST_FILENAME), json_encode($manifest));

        $this->assertFalse($this->manifestChecker->check(vfsStream::url('root')));
    }

    public function testReturnsTrueIfThePatchIsVerified()
    {
        $manifest = [
            'a.txt' => '86f7e437faa5a7fce15d1ddcb9eaeaea377667b8',
            'b.txt' => 'e9d71f5ee7c92d6dc9e92ffdad17b8bd49418f98',
            'c.txt' => '84a516841ba77a5b4648de2cd0dfcb30ea46dbb4',
        ];

        file_put_contents(vfsStream::url('root/' . ManifestChecker::MANIFEST_FILENAME), json_encode($manifest));

        $this->assertTrue($this->manifestChecker->check(vfsStream::url('root')));
    }

    // /**
    //  * @param string $workingDir
    //  */
    // public function check($workingDir)
    // {
    //     $manifestFile = $workingDir . '/' . self::MANIFEST_FILENAME;
    //     if (!file_exists($manifestFile)) {
    //         // The manifest did not exist
    //         return false;
    //     }

    //     $manifest = json_decode(file_get_contents($manifestFile));
    //     if (!$manifest) {
    //         $this->io->error('The package manifest could not be read.');

    //         return false;
    //     }

    //     $errors = [];
    //     foreach ($manifest as $path => $hash) {
    //         $workingPath = $workingDir . '/' . $path;
    //         if (!file_exists($workingPath)) {
    //             $errors[] = sprintf('"%s" does not exist', $path);
    //         }

    //         if (!hash_equals($hash, sha1_file($workingPath))) {
    //             $errors[] = sprintf('"%s" does not match the expected file contents', $path);
    //         }
    //     }

    //     if (!empty($errors)) {
    //         $this->io->error('The patch cannot be applied as the package could not be verified against the manifest.');
    //         $this->io->listing($errors);

    //         return false;
    //     }

    //     return true;
    // }
}
