<?php

namespace Meteor\Patch\Version;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class VersionComparerTest extends TestCase
{
    private $versionComparer;

    protected function setUp(): void
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

        static::assertInstanceOf('Meteor\Patch\Version\VersionDiff', $version);
        static::assertSame('jadu/cms', $version->getPackageName());
        static::assertSame('VERSION', $version->getFileName());
        static::assertSame('1.1.0', $version->getNewVersion());
        static::assertSame('0.1.2', $version->getCurrentVersion());
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

        static::assertCount(3, $versions);

        static::assertInstanceOf('Meteor\Patch\Version\VersionDiff', $versions[0]);
        static::assertSame('jadu/cms', $versions[0]->getPackageName());
        static::assertSame('VERSION', $versions[0]->getFileName());
        static::assertSame('1.1.0', $versions[0]->getNewVersion());
        static::assertSame('1.1.2', $versions[0]->getCurrentVersion());

        static::assertInstanceOf('Meteor\Patch\Version\VersionDiff', $versions[1]);
        static::assertSame('jadu/xfp', $versions[1]->getPackageName());
        static::assertSame('XFP_VERSION', $versions[1]->getFileName());
        static::assertSame('2.0.0', $versions[1]->getNewVersion());
        static::assertSame('3.0.0', $versions[1]->getCurrentVersion());

        static::assertInstanceOf('Meteor\Patch\Version\VersionDiff', $versions[2]);
        static::assertSame('jadu/cp', $versions[2]->getPackageName());
        static::assertSame('CP_VERSION', $versions[2]->getFileName());
        static::assertSame('0.0.1', $versions[2]->getNewVersion());
        static::assertSame('0.1.0', $versions[2]->getCurrentVersion());
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

        static::assertCount(1, $versions);

        static::assertInstanceOf('Meteor\Patch\Version\VersionDiff', $versions[0]);
        static::assertSame('jadu/cms', $versions[0]->getPackageName());
        static::assertSame('VERSION', $versions[0]->getFileName());
        static::assertSame('1.1.0', $versions[0]->getNewVersion());
        static::assertSame('1.1.2', $versions[0]->getCurrentVersion());
    }

    public function testCompareThrowsExceptionWhenUnableToFindVersionFileInWorkingDir()
    {
        static::expectException(RuntimeException::class);

        vfsStream::setup('root', null, [
            'working' => [],
            'install' => [],
        ]);

        $this->versionComparer->compare(vfsStream::url('root/working'), vfsStream::url('root/install'), 'test', 'VERSION');
    }
}
