<?php

namespace Meteor\Platform\Unix;

use Meteor\Permissions\Permission;
use Mockery;
use org\bovigo\vfs\vfsStream;

class UnixPlatformTest extends \PHPUnit_Framework_TestCase
{
    private $filesystem;
    private $installConfig;
    private $installConfigLoader;
    private $platform;

    public function setUp()
    {
        $this->filesystem = Mockery::mock('Meteor\Filesystem\Filesystem');
        $this->installConfig = Mockery::mock('Meteor\Platform\Unix\InstallConfig', [
            'getUser' => 'jadu',
            'getGroup' => 'jadu',
            'getWebUser' => 'jadu-www',
            'getWebGroup' => 'jadu-www',
        ]);
        $this->installConfigLoader = Mockery::mock('Meteor\Platform\Unix\InstallConfigLoader', [
            'load' => $this->installConfig,
        ]);

        $this->platform = new UnixPlatform($this->installConfigLoader, $this->filesystem);
        $this->platform->setInstallDir('install');

        vfsStream::setup('root', null, [
            'jadu' => [
                'custom' => [
                    'JaduCustom.php' => '<?php ?>',
                ],
                'JaduConstants.php' => '<?php ?>',
            ],
            'public_html' => [
                'file.txt' => 'Hello',
            ],
        ]);
    }

    public function testSetPermissionWithFile()
    {
        $permission = Permission::create('', ['r', 'w']);

        $this->filesystem->shouldReceive('chgrp')
            ->with('vfs://root/jadu/JaduConstants.php', 'jadu-www', false)
            ->once();

        $this->filesystem->shouldReceive('chmod')
            ->with('vfs://root/jadu/JaduConstants.php', 0660, 0000, false)
            ->once();

        $this->platform->setPermission(vfsStream::url('root/jadu/JaduConstants.php'), $permission);
    }

    public function testSetPermissionWithFileAndNoPermissions()
    {
        $permission = Permission::create('', []);

        $this->filesystem->shouldReceive('chgrp')
            ->with('vfs://root/jadu/JaduConstants.php', 'jadu-www', false)
            ->once();

        $this->filesystem->shouldReceive('chmod')
            ->with('vfs://root/jadu/JaduConstants.php', 0600, 0000, false)
            ->once();

        $this->platform->setPermission(vfsStream::url('root/jadu/JaduConstants.php'), $permission);
    }

    public function testSetPermissionWithDirectory()
    {
        $permission = Permission::create('', ['r', 'w', 'x', 'R']);

        $this->filesystem->shouldReceive('chgrp')
            ->with('vfs://root/jadu/custom', 'jadu-www', true)
            ->once();

        $this->filesystem->shouldReceive('chmod')
            ->with('vfs://root/jadu/custom', 0770, 0000, true)
            ->once();

        $this->platform->setPermission(vfsStream::url('root/jadu/custom'), $permission);
    }

    public function testSetPermissionWithDirectoryAndNoPermissions()
    {
        $permission = Permission::create('', []);

        $this->filesystem->shouldReceive('chgrp')
            ->with('vfs://root/jadu/custom', 'jadu-www', false)
            ->once();

        $this->filesystem->shouldReceive('chmod')
            ->with('vfs://root/jadu/custom', 0700, 0000, false)
            ->once();

        $this->platform->setPermission(vfsStream::url('root/jadu/custom'), $permission);
    }

    public function testSetPermissionWithFileIgnoresRecursiveFlag()
    {
        $permission = Permission::create('', ['r', 'w', 'x', 'R']);

        $this->filesystem->shouldReceive('chgrp')
            ->with('vfs://root/jadu/JaduConstants.php', 'jadu-www', false)
            ->once();

        $this->filesystem->shouldReceive('chmod')
            ->with('vfs://root/jadu/JaduConstants.php', 0670, 0000, false)
            ->once();

        $this->platform->setPermission(vfsStream::url('root/jadu/JaduConstants.php'), $permission);
    }

    public function testSetDefaultPermissionWithFile()
    {
        $this->filesystem->shouldReceive('chgrp')
            ->with('vfs://root/public_html/file.txt', 'jadu-www')
            ->once();

        $this->filesystem->shouldReceive('chown')
            ->with('vfs://root/public_html/file.txt', 'jadu')
            ->once();

        $this->filesystem->shouldReceive('chmod')
            ->with('vfs://root/public_html/file.txt', 0640)
            ->once();

        $this->platform->setDefaultPermission(vfsStream::url('root'), 'public_html/file.txt');
    }

    public function testSetDefaultPermissionWithDirectory()
    {
        $this->filesystem->shouldReceive('chgrp')
            ->with('vfs://root/public_html', 'jadu-www')
            ->once();

        $this->filesystem->shouldReceive('chown')
            ->with('vfs://root/public_html', 'jadu')
            ->once();

        $this->filesystem->shouldReceive('chmod')
            ->with('vfs://root/public_html', 0750)
            ->once();

        $this->platform->setDefaultPermission(vfsStream::url('root'), 'public_html');
    }

    public function testSetDefaultPermissionWithFileInJaduDirectory()
    {
        $this->filesystem->shouldReceive('chgrp')
            ->with('vfs://root/jadu/custom/JaduCustom.php', 'jadu')
            ->once();

        $this->filesystem->shouldReceive('chown')
            ->with('vfs://root/jadu/custom/JaduCustom.php', 'jadu')
            ->once();

        $this->filesystem->shouldReceive('chmod')
            ->with('vfs://root/jadu/custom/JaduCustom.php', 0640)
            ->once();

        $this->platform->setDefaultPermission(vfsStream::url('root'), 'jadu/custom/JaduCustom.php');
    }

    public function testSetDefaultPermissionWithDirectoryInJaduDirectory()
    {
        $this->filesystem->shouldReceive('chgrp')
            ->with('vfs://root/jadu/custom', 'jadu')
            ->once();

        $this->filesystem->shouldReceive('chown')
            ->with('vfs://root/jadu/custom', 'jadu')
            ->once();

        $this->filesystem->shouldReceive('chmod')
            ->with('vfs://root/jadu/custom', 0750)
            ->once();

        $this->platform->setDefaultPermission(vfsStream::url('root'), 'jadu/custom');
    }
}
