<?php

namespace Meteor\Patch\Backup;

use Meteor\Configuration\Exception\ConfigurationLoadingException;
use Meteor\Patch\Version\VersionComparer;
use Mockery;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class BackupFinderTest extends \PHPUnit_Framework_TestCase
{
    private $configurationLoader;
    private $backupFinder;

    public function setUp()
    {
        $this->configurationLoader = Mockery::mock('Meteor\Configuration\ConfigurationLoader');
        $this->backupFinder = new BackupFinder(new VersionComparer(), $this->configurationLoader);

        vfsStream::setup('root', null, [
            'backups' => [],
        ]);
    }

    public function testFind()
    {
        $config = [
            'name' => 'jadu/cms',
            'package' => [
                'version' => 'VERSION',
            ],
        ];

        vfsStream::setup('root', null, [
            'backups' => [
                '20160701102030' => [
                    'to_patch' => [
                        'VERSION' => '1.0.0',
                    ],
                ],
            ],
            'VERSION' => '1.1.0',
        ]);

        $this->configurationLoader->shouldReceive('load')
            ->with(vfsStream::url('root/backups/20160701102030'))
            ->andReturn([
                'name' => 'jadu/cms',
            ])
            ->once();

        $backups = $this->backupFinder->find(vfsStream::url('root'), $config);

        $this->assertCount(1, $backups);
        $backup = $backups[0];

        $this->assertInstanceOf('Meteor\Patch\Backup\Backup', $backup);
        $this->assertSame(vfsStream::url('root/backups/20160701102030'), $backup->getPath());

        $backupVersions = $backup->getVersions();
        $this->assertCount(1, $backupVersions);
        $this->assertSame('jadu/cms', $backupVersions[0]->getPackageName());
        $this->assertSame('VERSION', $backupVersions[0]->getFileName());
        $this->assertSame('1.0.0', $backupVersions[0]->getNewVersion());
        $this->assertSame('1.1.0', $backupVersions[0]->getCurrentVersion());
    }

    public function testFindIgnoresBackupsWithoutMeteorConfig()
    {
        $config = [
            'name' => 'jadu/cms',
            'package' => [
                'version' => 'VERSION',
            ],
        ];

        vfsStream::setup('root', null, [
            'backups' => [
                '20160701102030' => [
                    'to_patch' => [
                        'VERSION' => '1.0.0',
                    ],
                ],
            ],
            'VERSION' => '1.1.0',
        ]);

        $this->configurationLoader->shouldReceive('load')
            ->with(vfsStream::url('root/backups/20160701102030'))
            ->andThrow(new ConfigurationLoadingException())
            ->once();

        $backups = $this->backupFinder->find(vfsStream::url('root'), $config);

        $this->assertCount(0, $backups);
    }

    public function testFindIgnoresBackupsWithAnInvalidMeteorConfig()
    {
        $config = [
            'name' => 'jadu/cms',
            'package' => [
                'version' => 'VERSION',
            ],
        ];

        vfsStream::setup('root', null, [
            'backups' => [
                '20160701102030' => [
                    'to_patch' => [
                        'VERSION' => '1.0.0',
                    ],
                ],
            ],
            'VERSION' => '1.1.0',
        ]);

        $this->configurationLoader->shouldReceive('load')
            ->with(vfsStream::url('root/backups/20160701102030'))
            ->andThrow(new InvalidConfigurationException())
            ->once();

        $backups = $this->backupFinder->find(vfsStream::url('root'), $config);

        $this->assertCount(0, $backups);
    }

    public function testFindWithCombinedPackages()
    {
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

        vfsStream::setup('root', null, [
            'backups' => [
                '20160701102030' => [
                    'to_patch' => [
                        'VERSION' => '1.0.0',
                        'XFP_VERSION' => '1.0.0',
                        'CP_VERSION' => '1.0.0',
                    ],
                ],
            ],
            'VERSION' => '1.1.0',
            'XFP_VERSION' => '1.1.0',
            'CP_VERSION' => '1.1.0',
        ]);

        $this->configurationLoader->shouldReceive('load')
            ->with(vfsStream::url('root/backups/20160701102030'))
            ->andReturn([
                'name' => 'jadu/cms',
                'combined' => [
                    [
                        'name' => 'jadu/cp',
                    ],
                    [
                        'name' => 'jadu/xfp',
                    ],
                ],
            ])
            ->once();

        $backups = $this->backupFinder->find(vfsStream::url('root'), $config);

        $this->assertCount(1, $backups);
        $backup = $backups[0];

        $this->assertInstanceOf('Meteor\Patch\Backup\Backup', $backup);
        $this->assertSame(vfsStream::url('root/backups/20160701102030'), $backup->getPath());

        $backupVersions = $backup->getVersions();
        $this->assertCount(3, $backupVersions);

        $this->assertSame('jadu/cms', $backupVersions[0]->getPackageName());
        $this->assertSame('VERSION', $backupVersions[0]->getFileName());
        $this->assertSame('1.0.0', $backupVersions[0]->getNewVersion());
        $this->assertSame('1.1.0', $backupVersions[0]->getCurrentVersion());

        $this->assertSame('jadu/xfp', $backupVersions[1]->getPackageName());
        $this->assertSame('XFP_VERSION', $backupVersions[1]->getFileName());
        $this->assertSame('1.0.0', $backupVersions[1]->getNewVersion());
        $this->assertSame('1.1.0', $backupVersions[1]->getCurrentVersion());

        $this->assertSame('jadu/cp', $backupVersions[2]->getPackageName());
        $this->assertSame('CP_VERSION', $backupVersions[2]->getFileName());
        $this->assertSame('1.0.0', $backupVersions[2]->getNewVersion());
        $this->assertSame('1.1.0', $backupVersions[2]->getCurrentVersion());
    }

    public function testFindWithDifferentCombinedPackages()
    {
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

        vfsStream::setup('root', null, [
            'backups' => [
                '20160701102030' => [
                    'to_patch' => [
                        'VERSION' => '1.0.0',
                        'XFP_VERSION' => '1.0.0',
                        'CP_VERSION' => '1.0.0',
                    ],
                ],
            ],
            'VERSION' => '1.1.0',
            'XFP_VERSION' => '1.1.0',
            'CP_VERSION' => '1.1.0',
        ]);

        $this->configurationLoader->shouldReceive('load')
            ->with(vfsStream::url('root/backups/20160701102030'))
            ->andReturn([
                'name' => 'jadu/cms',
                'combined' => [
                    [
                        'name' => 'jadu/xfp',
                    ],
                    [
                        'name' => 'jadu/poo',
                    ],
                ],
            ])
            ->once();

        $backups = $this->backupFinder->find(vfsStream::url('root'), $config);

        $this->assertCount(0, $backups);
    }

    public function testFindWhenBackupHasDifferentPackages()
    {
        $config = [
            'name' => 'jadu/cms',
            'package' => [
                'version' => 'VERSION',
            ],
        ];

        vfsStream::setup('root', null, [
            'backups' => [
                '20160701102030' => [
                    'to_patch' => [
                        'VERSION' => '1.0.0',
                    ],
                ],
            ],
            'VERSION' => '1.1.0',
        ]);

        $this->configurationLoader->shouldReceive('load')
            ->with(vfsStream::url('root/backups/20160701102030'))
            ->andReturn([
                'name' => 'jadu/xfp',
            ])
            ->once();

        $backups = $this->backupFinder->find(vfsStream::url('root'), $config);

        $this->assertCount(0, $backups);
    }

    public function testFindWhenBackupIsNewerThanInstall()
    {
        $config = [
            'name' => 'jadu/cms',
            'package' => [
                'version' => 'VERSION',
            ],
        ];

        vfsStream::setup('root', null, [
            'backups' => [
                '20160701102030' => [
                    'to_patch' => [
                        'VERSION' => '1.1.0',
                    ],
                ],
            ],
            'VERSION' => '1.0.0',
        ]);

        $this->configurationLoader->shouldReceive('load')
            ->with(vfsStream::url('root/backups/20160701102030'))
            ->andReturn([
                'name' => 'jadu/cms',
            ])
            ->once();

        $backups = $this->backupFinder->find(vfsStream::url('root'), $config);

        $this->assertCount(0, $backups);
    }
}
