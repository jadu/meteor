<?php

namespace Meteor\Permissions;

use PHPUnit\Framework\TestCase;

class PermissionLoaderTest extends TestCase
{
    private $loader;

    protected function setUp(): void
    {
        $this->loader = new PermissionLoader();
    }

    public function testLoad()
    {
        $permissions = $this->loader->load(__DIR__ . '/Fixtures');

        static::assertEquals(Permission::create('public_html/.htaccess*', ['r', 'w']), $permissions[0]);
    }

    public function testLoadIgnoresExtraWhitespace()
    {
        $permissions = $this->loader->load(__DIR__ . '/Fixtures');

        static::assertEquals(Permission::create('var/tmp', ['r', 'w', 'x', 'R']), $permissions[38]);
    }

    public function testLoadFromArrayReturnsEmpty()
    {
        static::assertEmpty($this->loader->loadFromArray([]));
    }

    public function testLoadFromArrayReturnsPermissions()
    {
        $data = [
            'var/cache/*' => 'rwxR',
        ];

        $permissions = $this->loader->loadFromArray($data);
        static::assertCount(1, $permissions);

        $permission = $permissions[0];
        static::assertEquals('var/cache/*', $permission->getPattern());
    }

    public function testLoadFromArrayDoesntReturnIfPatternDoesntMatch()
    {
        $data = [
            'var/cache/*' => 'rwxRRG',
        ];

        $permissions = $this->loader->loadFromArray($data);
        static::assertCount(0, $permissions);
    }
}
