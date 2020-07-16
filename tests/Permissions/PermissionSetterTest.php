<?php

namespace Meteor\Permissions;

use Exception;
use Meteor\IO\NullIO;
use Mockery;
use org\bovigo\vfs\vfsStream;

class PermissionSetterTest extends \PHPUnit_Framework_TestCase
{
    private $platform;
    private $permissionLoader;
    private $io;
    private $permissionSetter;

    public function setUp()
    {
        $this->platform = Mockery::mock('Meteor\Platform\PlatformInterface');
        $this->permissionLoader = Mockery::mock('Meteor\Permissions\PermissionLoader');
        $this->io = new NullIO();
        $this->permissionSetter = new PermissionSetter($this->platform, $this->permissionLoader, $this->io);
    }

    public function testSetDefaultPermissions()
    {
        $files = [
            'test',
        ];

        $this->platform->shouldReceive('setDefaultPermission')
            ->with('target', 'test')
            ->once();

        $this->permissionSetter->setDefaultPermissions($files, 'target');
    }

    public function testSetDefaultPermissionsCatchesExceptions()
    {
        $files = [
            'test',
        ];

        $this->platform->shouldReceive('setDefaultPermission')
            ->andThrow(new Exception())
            ->once();

        $this->permissionSetter->setDefaultPermissions($files, 'target');
    }

    public function testSetPermissions()
    {
        vfsStream::setup('root', null, [
            'base' => [
                'var' => [
                    'config' => [
                        'system.xml' => '',
                    ],
                ],
            ],
            'target' => [
                'var' => [
                    'config' => [
                        'system.xml' => '',
                    ],
                ],
            ],
        ]);

        $permission = new Permission('var/config');

        $this->permissionLoader->shouldReceive('load')
            ->with(vfsStream::url('root/target'))
            ->andReturn([$permission])
            ->once();

        $this->platform->shouldReceive('setPermission')
            ->with(vfsStream::url('root/target/var/config'), $permission)
            ->once();

        $this->permissionSetter->setPermissions(vfsStream::url('root/base'), vfsStream::url('root/target'));
    }

    public function testSetPermissionsWhenPathDoesNotExistInBaseDir()
    {
        vfsStream::setup('root', null, [
            'base' => [
                'var' => [],
            ],
            'target' => [
                'var' => [
                    'config' => [
                        'system.xml' => '',
                    ],
                ],
            ],
        ]);

        $permission = new Permission('var/config');

        $this->permissionLoader->shouldReceive('load')
            ->with(vfsStream::url('root/target'))
            ->andReturn([$permission])
            ->once();

        $this->platform->shouldReceive('setPermission')
            ->with(vfsStream::url('root/target/var/config'), $permission)
            ->never();

        $this->permissionSetter->setPermissions(vfsStream::url('root/base'), vfsStream::url('root/target'));
    }

    public function testSetPermissionsWithWildcardPattern()
    {
        vfsStream::setup('root', null, [
            'base' => [
                'var' => [
                    'config' => [
                        'system.xml' => '',
                        'constants.xml' => '',
                    ],
                ],
            ],
            'target' => [
                'var' => [
                    'config' => [
                        'system.xml' => '',
                        'constants.xml' => '',
                    ],
                ],
            ],
        ]);

        $permission = new Permission('var/config/*.xml');

        $this->permissionLoader->shouldReceive('load')
            ->with(vfsStream::url('root/target'))
            ->andReturn([$permission])
            ->once();

        $this->platform->shouldReceive('setPermission')
            ->with(vfsStream::url('root/target/var/config/system.xml'), $permission)
            ->once();

        $this->platform->shouldReceive('setPermission')
            ->with(vfsStream::url('root/target/var/config/constants.xml'), $permission)
            ->once();

        $this->permissionSetter->setPermissions(vfsStream::url('root/base'), vfsStream::url('root/target'));
    }

    public function testSetPermissionsWithWildcardPatternWhenPathDoesNotExistInBaseDir()
    {
        vfsStream::setup('root', null, [
            'base' => [
                'var' => [
                    'config' => [],
                ],
            ],
            'target' => [
                'var' => [
                    'config' => [
                        'system.xml' => '',
                        'constants.xml' => '',
                    ],
                ],
            ],
        ]);

        $permission = new Permission('var/config/*.xml');

        $this->permissionLoader->shouldReceive('load')
            ->with(vfsStream::url('root/target'))
            ->andReturn([$permission])
            ->once();

        $this->platform->shouldReceive('setPermission')
            ->never();

        $this->permissionSetter->setPermissions(vfsStream::url('root/base'), vfsStream::url('root/target'));
    }

    public function testSetPermissionsCatchesExceptions()
    {
        vfsStream::setup('root', null, [
            'target' => [
                'var' => [
                    'cache' => [
                        'test.xml' => '',
                    ],
                ],
            ],
        ]);

        $permission = new Permission('var/cache/*');

        $this->permissionLoader->shouldReceive('loadFromArray')
            ->with($this->permissionSetter->getPostScriptsPermissions())
            ->andReturn([$permission])
            ->once();

        $this->platform->shouldReceive('setPermission')
            ->andThrow(new Exception())
            ->once();

        $this->permissionSetter->setPostScriptsPermissions(vfsStream::url('root/target'));
    }

    public function testSetPostApplyPermissions()
    {
        vfsStream::setup('root', null, [
            'target' => [
                'var' => [
                    'cache' => [
                        'test.php' => '',
                    ],
                ],
            ],
        ]);

        $permission = new Permission('var/cache/*');

        $this->permissionLoader->shouldReceive('loadFromArray')
            ->with($this->permissionSetter->getPostScriptsPermissions())
            ->andReturn([$permission])
            ->once();

        $this->platform->shouldReceive('setPermission')
            ->with(vfsStream::url('root/target/var/cache/test.php'), $permission)
            ->once();

        $this->permissionSetter->setPostScriptsPermissions(vfsStream::url('root/target'));
    }

}
