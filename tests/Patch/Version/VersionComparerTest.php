<?php

namespace Meteor\Patch\Version;

use org\bovigo\vfs\vfsStream;

class VersionComparerTest extends \PHPUnit_Framework_TestCase
{
    private $versionComparer;

    public function setUp()
    {
        $this->versionComparer = new VersionComparer();
    }

    public function testCompare()
    {
        vfsStream::setup('root', null, [
            'working' => [
                'VERSION' => '1.1.0',
            ],
            'install' => [
                'VERSION' => '0.1.2',
            ],
        ]);

        $version = $this->versionComparer->compare(vfsStream::url('root/working'), vfsStream::url('root/install'), 'jadu/cms', 'VERSION');

        $this->assertInstanceOf('Meteor\Patch\Version\VersionDiff', $version);
        $this->assertSame('jadu/cms', $version->getPackageName());
        $this->assertSame('VERSION', $version->getFileName());
        $this->assertSame('1.1.0', $version->getNewVersion());
        $this->assertSame('0.1.2', $version->getCurrentVersion());
    }

    public function testComparePackage()
    {
        vfsStream::setup('root', null, [
            'working' => [
                'VERSION' => '1.1.0',
                'XFP_VERSION' => '2.0.0',
                'CP_VERSION' => '0.0.1',
            ],
            'install' => [
                'VERSION' => '1.1.2',
                'XFP_VERSION' => '3.0.0',
                'CP_VERSION' => '0.1.0',
            ],
        ]);

        $config = [
            'name' => 'jadu/cms',
            'package' => [
                'version' => 'VERSION',
            ],
            'combined' => [
                [
                    'name' => 'jadu/xfp',
                    'package' => [
                        'version' => 'XFP_VERSION',
                    ],
                ],
                [
                    'name' => 'jadu/cp',
                    'package' => [
                        'version' => 'CP_VERSION',
                    ],
                ],
            ],
        ];

        $versions = $this->versionComparer->comparePackage(vfsStream::url('root/working'), vfsStream::url('root/install'), $config);

        $this->assertCount(3, $versions);

        $this->assertInstanceOf('Meteor\Patch\Version\VersionDiff', $versions[0]);
        $this->assertSame('jadu/cms', $versions[0]->getPackageName());
        $this->assertSame('VERSION', $versions[0]->getFileName());
        $this->assertSame('1.1.0', $versions[0]->getNewVersion());
        $this->assertSame('1.1.2', $versions[0]->getCurrentVersion());

        $this->assertInstanceOf('Meteor\Patch\Version\VersionDiff', $versions[1]);
        $this->assertSame('jadu/xfp', $versions[1]->getPackageName());
        $this->assertSame('XFP_VERSION', $versions[1]->getFileName());
        $this->assertSame('2.0.0', $versions[1]->getNewVersion());
        $this->assertSame('3.0.0', $versions[1]->getCurrentVersion());

        $this->assertInstanceOf('Meteor\Patch\Version\VersionDiff', $versions[2]);
        $this->assertSame('jadu/cp', $versions[2]->getPackageName());
        $this->assertSame('CP_VERSION', $versions[2]->getFileName());
        $this->assertSame('0.0.1', $versions[2]->getNewVersion());
        $this->assertSame('0.1.0', $versions[2]->getCurrentVersion());
    }

    public function testComparePackageIgnoresVersionFilesThatDoNotExistInThePatchDir()
    {
        vfsStream::setup('root', null, [
            'working' => [
                'VERSION' => '1.1.0',
            ],
            'install' => [
                'VERSION' => '1.1.2',
                'XFP_VERSION' => '3.0.0',
                'CP_VERSION' => '0.1.0',
            ],
        ]);

        $config = [
            'name' => 'jadu/cms',
            'package' => [
                'version' => 'VERSION',
            ],
            'combined' => [
                [
                    'name' => 'jadu/xfp',
                    'package' => [
                        'version' => 'XFP_VERSION',
                    ],
                ],
                [
                    'name' => 'jadu/cp',
                    'package' => [
                        'version' => 'CP_VERSION',
                    ],
                ],
            ],
        ];

        $versions = $this->versionComparer->comparePackage(vfsStream::url('root/working'), vfsStream::url('root/install'), $config);

        $this->assertCount(1, $versions);

        $this->assertInstanceOf('Meteor\Patch\Version\VersionDiff', $versions[0]);
        $this->assertSame('jadu/cms', $versions[0]->getPackageName());
        $this->assertSame('VERSION', $versions[0]->getFileName());
        $this->assertSame('1.1.0', $versions[0]->getNewVersion());
        $this->assertSame('1.1.2', $versions[0]->getCurrentVersion());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCompareThrowsExceptionWhenUnableToFindVersionFileInWorkingDir()
    {
        vfsStream::setup('root', null, [
            'working' => [],
            'install' => [],
        ]);

        $this->versionComparer->compare(vfsStream::url('root/working'), vfsStream::url('root/install'), 'test', 'VERSION');
    }
}
