<?php

namespace Meteor\Permissions;

class PermissionLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $loader;

    public function setUp()
    {
        $this->loader = new PermissionLoader();
    }

    public function testLoad()
    {
        $permissions = $this->loader->load(__DIR__ . '/Fixtures');

        $this->assertEquals(Permission::create('public_html/.htaccess*', ['r', 'w']), $permissions[0]);
    }

    public function testLoadIgnoresExtraWhitespace()
    {
        $permissions = $this->loader->load(__DIR__ . '/Fixtures');

        $this->assertEquals(Permission::create('var/tmp', ['r', 'w', 'x', 'R']), $permissions[38]);
    }

    public function testLoadFromArrayReturnsEmpty()
    {
        $this->assertEmpty($this->loader->loadFromArray([]));
    }

    public function testLoadFromArrayReturnsPermissions()
    {
        $data = [
            'var/cache/*'=> 'rwxR'
        ];

        $permissions = $this->loader->loadFromArray($data);
        $this->assertCount(1, $permissions);

        $permission = $permissions[0];
        $this->assertEquals('var/cache/*', $permission->getPattern());
    }


    public function testLoadFromArrayDoesntReturnIfPatternDoesntMatch()
    {
        $data = [
            'var/cache/*'=> 'rwxRRG'
        ];

        $permissions = $this->loader->loadFromArray($data);
        $this->assertCount(0, $permissions);
    }
}
