<?php

namespace Meteor\Permissions;

class PermissionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateWithAllPermissions()
    {
        $expectedPermission = new Permission('test/*');
        $expectedPermission->setRead(true);
        $expectedPermission->setWrite(true);
        $expectedPermission->setExecute(true);
        $expectedPermission->setRecursive(true);

        $this->assertEquals($expectedPermission, Permission::create('test/*', ['r', 'w', 'x', 'R']));
    }

    public function testCreateWithNoPermissions()
    {
        $expectedPermission = new Permission('test/*');
        $expectedPermission->setRead(false);
        $expectedPermission->setWrite(false);
        $expectedPermission->setExecute(false);
        $expectedPermission->setRecursive(false);

        $this->assertEquals($expectedPermission, Permission::create('test/*', []));
    }

    public function testCreateWithSomePermissions()
    {
        $expectedPermission = new Permission('test/*');
        $expectedPermission->setRead(true);
        $expectedPermission->setWrite(false);
        $expectedPermission->setExecute(true);
        $expectedPermission->setRecursive(false);

        $this->assertEquals($expectedPermission, Permission::create('test/*', ['r', 'x']));
    }

    public function testCreateWithExtraWhitespaceInPath()
    {
        $expectedPermission = new Permission('    test/*    ');

        $this->assertEquals($expectedPermission, Permission::create('test/*', []));
    }

    /**
     * @dataProvider matchesProvider
     */
    public function testMatches($pattern, $path, $expectedResult)
    {
        $permission = new Permission($pattern);
        $this->assertSame($expectedResult, $permission->matches($path));
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
