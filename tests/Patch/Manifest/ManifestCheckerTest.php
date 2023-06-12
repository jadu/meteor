<?php

namespace Meteor\Patch\Manifest;

use Meteor\IO\NullIO;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ManifestCheckerTest extends TestCase
{
    private $manifestChecker;

    protected function setUp(): void
    {
        $this->manifestChecker = new ManifestChecker(new NullIO());

        vfsStream::setup('root', null, [
            'a.txt' => 'a',
            'b.txt' => 'b',
            'c.txt' => 'c',
        ]);
    }

    public function testReturnsTrueIfManifestFileDoesNotExist()
    {
        static::assertTrue($this->manifestChecker->check(vfsStream::url('root')));
    }

    public function testReturnsFalseIfManifestFileCouldNotBeParsed()
    {
        file_put_contents(vfsStream::url('root/' . ManifestChecker::MANIFEST_FILENAME), '!!');

        static::assertFalse($this->manifestChecker->check(vfsStream::url('root')));
    }

    public function testReturnsFalseIfThePatchCannotBeVerifiedDueToMissingFile()
    {
        $manifest = [
            'd.txt' => 'hash',
        ];

        file_put_contents(vfsStream::url('root/' . ManifestChecker::MANIFEST_FILENAME), json_encode($manifest));

        static::assertFalse($this->manifestChecker->check(vfsStream::url('root')));
    }

    public function testReturnsFalseIfThePatchCannotBeVerifiedDueToInvalidFileHash()
    {
        $manifest = [
            'c.txt' => 'hash',
        ];

        file_put_contents(vfsStream::url('root/' . ManifestChecker::MANIFEST_FILENAME), json_encode($manifest));

        static::assertFalse($this->manifestChecker->check(vfsStream::url('root')));
    }

    public function testReturnsTrueIfThePatchIsVerified()
    {
        $manifest = [
            'a.txt' => '86f7e437faa5a7fce15d1ddcb9eaeaea377667b8',
            'b.txt' => 'e9d71f5ee7c92d6dc9e92ffdad17b8bd49418f98',
            'c.txt' => '84a516841ba77a5b4648de2cd0dfcb30ea46dbb4',
        ];

        file_put_contents(vfsStream::url('root/' . ManifestChecker::MANIFEST_FILENAME), json_encode($manifest));

        static::assertTrue($this->manifestChecker->check(vfsStream::url('root')));
    }
}
