<?php

namespace Meteor\Platform\Windows;

use Meteor\Permissions\Permission;
use Mockery;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class WindowsPlatformTest extends TestCase
{
    private $processRunner;
    private $platform;

    protected function setUp(): void
    {
        $this->processRunner = Mockery::mock('Meteor\Process\ProcessRunner');
        $this->platform = new WindowsPlatform($this->processRunner);

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
        $permission = Permission::create('', ['r', 'w', 'x']);

        $this->processRunner->shouldReceive('run')
            ->andReturnUsing(function ($command) {
                static::assertEquals(['icacls', 'vfs://root/jadu/JaduConstants.php', '/remove:g', 'IIS_IUSRS', '/grant', 'IIS_IUSRS:RXWM', '/Q'], $command);
            })
            ->once();
        $this->platform->setPermission(vfsStream::url('root/jadu/JaduConstants.php'), $permission);
    }

    public function testSetPermissionWithDirectory()
    {
        $permission = Permission::create('', ['r', 'w', 'x']);

        $this->processRunner->shouldReceive('run')
            ->andReturnUsing(function ($command) {
                static::assertEquals(['icacls', 'vfs://root/jadu/custom', '/remove:g', 'IIS_IUSRS', '/grant', 'IIS_IUSRS:(OI)(CI)RXWM', '/Q'], $command);
            })
            ->once();

        $this->platform->setPermission(vfsStream::url('root/jadu/custom'), $permission);
    }

    public function testSetPermissionWithDirectoryAndRecursive()
    {
        $permission = Permission::create('', ['r', 'w', 'x', 'R']);

        $this->processRunner->shouldReceive('run')
            ->andReturnUsing(function ($command) {
                static::assertEquals(['icacls', 'vfs://root/jadu/custom', '/remove:g', 'IIS_IUSRS', '/grant', 'IIS_IUSRS:(OI)(CI)RXWM', '/t', '/Q'], $command);
            })
            ->once();

        $this->platform->setPermission(vfsStream::url('root/jadu/custom'), $permission);
    }

    public function testSetPermissionWithFileIgnoresRecursiveFlag()
    {
        $permission = Permission::create('', ['r', 'w', 'x', 'R']);

        $this->processRunner->shouldReceive('run')
            ->andReturnUsing(function ($command) {
                static::assertEquals(['icacls', 'vfs://root/jadu/JaduConstants.php', '/remove:g', 'IIS_IUSRS', '/grant', 'IIS_IUSRS:RXWM', '/Q'], $command);
            })
            ->once();

        $this->platform->setPermission(vfsStream::url('root/jadu/JaduConstants.php'), $permission);
    }

    public function testSetPermissionWithExecuteButNotWritePermission()
    {
        $permission = Permission::create('', ['x']);

        $this->processRunner->shouldReceive('run')
            ->andReturnUsing(function ($command) {
                static::assertEquals(['icacls', 'vfs://root/jadu/JaduConstants.php', '/remove:g', 'IIS_IUSRS', '/grant', 'IIS_IUSRS:RX', '/Q'], $command);
            })
            ->once();

        $this->platform->setPermission(vfsStream::url('root/jadu/JaduConstants.php'), $permission);
    }
}
