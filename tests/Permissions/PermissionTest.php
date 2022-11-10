<?php

namespace Meteor\Permissions;

use PHPUnit\Framework\TestCase;

class PermissionTest extends TestCase
{
    public function testCreateWithAllPermissions()
    {
        $expectedPermission = new Permission('test/*');
        $expectedPermission->setRead(true);
        $expectedPermission->setWrite(true);
        $expectedPermission->setExecute(true);
        $expectedPermission->setRecursive(true);

        static::assertEquals($expectedPermission, Permission::create('test/*', ['r', 'w', 'x', 'R']));
    }

    public function testCreateWithNoPermissions()
    {
        $expectedPermission = new Permission('test/*');
        $expectedPermission->setRead(false);
        $expectedPermission->setWrite(false);
        $expectedPermission->setExecute(false);
        $expectedPermission->setRecursive(false);

        static::assertEquals($expectedPermission, Permission::create('test/*', []));
    }

    public function testCreateWithSomePermissions()
    {
        $expectedPermission = new Permission('test/*');
        $expectedPermission->setRead(true);
        $expectedPermission->setWrite(false);
        $expectedPermission->setExecute(true);
        $expectedPermission->setRecursive(false);

        static::assertEquals($expectedPermission, Permission::create('test/*', ['r', 'x']));
    }

    public function testCreateWithExtraWhitespaceInPath()
    {
        $expectedPermission = new Permission('    test/*    ');

        static::assertEquals($expectedPermission, Permission::create('test/*', []));
    }

    /**
     * @dataProvider matchesProvider
     */
    public function testMatches($pattern, $path, $expectedResult)
    {
        $permission = new Permission($pattern);
        static::assertSame($expectedResult, $permission->matches($path));
    }

    public function matchesProvider()
    {
        return [
            ['test', 'test', true],
            ['test', 'other/test', false],
            ['config/*.xml', 'config/system.xml', true],
            ['config/*.xml', 'config/system.xml.dist', false],
        ];
    }
}
