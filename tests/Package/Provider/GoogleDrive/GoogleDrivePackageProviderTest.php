<?php

namespace Meteor\Package\Provider\GoogleDrive;

use Meteor\IO\NullIO;
use Meteor\Package\Provider\Exception\PackageNotFoundException;
use Meteor\Process\ProcessRunner;
use Mockery;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class GoogleDrivePackageProviderTest extends TestCase
{
    private $processRunner;
    private $packageProvider;

    protected function setUp(): void
    {
        $this->processRunner = Mockery::mock(ProcessRunner::class);
        $this->packageProvider = new GoogleDrivePackageProvider($this->processRunner, new NullIO(), 'gdrive', [
            'jadu/cms' => '12345',
        ]);

        vfsStream::setup('root');
    }

    public function testDownload()
    {
        // Create the file as if it was downloaded
        vfsStream::setup('root', null, [
            '3.2.1.zip' => '',
        ]);

        $this->processRunner->shouldReceive('run')
            ->with("gdrive download query 'name = '\''3.2.1.zip'\'' and '\''12345'\'' in parents' --force", vfsStream::url('root'))
            ->once();

        $this->packageProvider->download('jadu/cms', '3.2.1', vfsStream::url('root'));
    }

    public function testDownloadThrowsExceptionWhenPackageFolderNotConfigured()
    {
        static::expectException(PackageNotFoundException::class);

        $this->packageProvider->download('jadu/xfp', '3.2.1', vfsStream::url('root'));
    }

    public function testDownloadThrowsExceptionWhenPackageNotDownloaded()
    {
        static::expectException(PackageNotFoundException::class);

        $this->processRunner->shouldReceive('run')
            ->once();

        $this->packageProvider->download('jadu/cms', '3.2.1', vfsStream::url('root'));
    }
}
