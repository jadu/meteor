<?php

namespace Meteor\Permissions\Cli\Command;

use Meteor\Cli\Command\CommandTestCase;
use Meteor\Filesystem\Filesystem;
use Meteor\IO\NullIO;
use Meteor\Permissions\PermissionSetter;
use Meteor\Platform\PlatformInterface;
use Mockery;
use org\bovigo\vfs\vfsStream;

class ResetPermissionsCommandTest extends CommandTestCase
{
    private $platform;
    private $permissionSetter;

    public function createCommand()
    {
        vfsStream::setup('root', null, [
            'patch' => [],
            'install' => [],
        ]);

        $this->platform = Mockery::mock(PlatformInterface::class, [
            'setInstallDir' => null,
        ]);

        $this->filesystem = Mockery::mock(Filesystem::class);
        $this->permissionSetter = Mockery::mock(PermissionSetter::class);

        return new ResetPermissionsCommand(
            null,
            [],
            new NullIO(),
            $this->platform,
            $this->filesystem,
            $this->permissionSetter
        );
    }

    public function testResetsPermissions()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = ['name' => 'test'];
        $this->command->setConfiguration($config);

        $this->platform->shouldReceive('setInstallDir')
            ->with($installDir)
            ->once();

        $this->permissionSetter->shouldReceive('setPermissions')
            ->with($installDir, $installDir)
            ->once();

        $this->tester->execute([
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ]);
    }

    public function testResetsDefaultPermissions()
    {
        $workingDir = vfsStream::url('root/patch');
        $installDir = vfsStream::url('root/install');

        $config = ['name' => 'test'];
        $this->command->setConfiguration($config);

        $this->platform->shouldReceive('setInstallDir')
            ->with($installDir)
            ->once();

        $files = ['a.txt', 'b.txt'];
        $this->filesystem->shouldReceive('findFiles')
            ->with($workingDir)
            ->andReturn($files)
            ->once();

        $this->permissionSetter->shouldReceive('setDefaultPermissions')
            ->with($files, $installDir)
            ->once()
            ->ordered();

        $this->permissionSetter->shouldReceive('setPermissions')
            ->with($installDir, $installDir)
            ->once()
            ->ordered();

        $this->tester->execute([
            '--default' => true,
            '--working-dir' => $workingDir,
            '--install-dir' => $installDir,
        ]);
    }
}
