<?php

namespace Meteor\Package\Provider\GoogleDrive;

use Meteor\IO\NullIO;
use Mockery;
use org\bovigo\vfs\vfsStream;

class GoogleDrivePackageProviderTest extends \PHPUnit_Framework_TestCase
{
    private $processRunner;
    private $packageProvider;

    public function setUp()
    {
        $this->processRunner = Mockery::mock('Meteor\Process\ProcessRunner');
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

    /**
     * @expectedException \Meteor\Package\Provider\Exception\PackageNotFoundException
     */
    public function testDownloadThrowsExceptionWhenPackageFolderNotConfigured()
    {
        $this->packageProvider->download('jadu/xfp', '3.2.1', vfsStream::url('root'));
    }

    /**
     * @expectedException \Meteor\Package\Provider\Exception\PackageNotFoundException
     */
    public function testDownloadThrowsExceptionWhenPackageNotDownloaded()
    {
        $this->processRunner->shouldReceive('run')
            ->once();

        $this->packageProvider->download('jadu/cms', '3.2.1', vfsStream::url('root'));
    }
}
