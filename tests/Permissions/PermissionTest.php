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

        $this->assertEquals($expectedPermission, Permission::create('test/*', array('r', 'w', 'x', 'R')));
    }

    public function testCreateWithNoPermissions()
    {
        $expectedPermission = new Permission('test/*');
        $expectedPermission->setRead(false);
        $expectedPermission->setWrite(false);
        $expectedPermission->setExecute(false);
        $expectedPermission->setRecursive(false);

        $this->assertEquals($expectedPermission, Permission::create('test/*', array()));
    }

    public function testCreateWithSomePermissions()
    {
        $expectedPermission = new Permission('test/*');
        $expectedPermission->setRead(true);
        $expectedPermission->setWrite(false);
        $expectedPermission->setExecute(true);
        $expectedPermission->setRecursive(false);

        $this->assertEquals($expectedPermission, Permission::create('test/*', array('r', 'x')));
    }

    public function testCreateWithExtraWhitespaceInPath()
    {
        $expectedPermission = new Permission('    test/*    ');

        $this->assertEquals($expectedPermission, Permission::create('test/*', array()));
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
        return array(
            array('test', 'test', true),
            array('test', 'other/test', false),
            array('config/*.xml', 'config/system.xml', true),
            array('config/*.xml', 'config/system.xml.dist', false),
        );
    }
}
