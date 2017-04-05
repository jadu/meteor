<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use Mockery;

class SetPermissionsHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $io;
    private $permissionSetter;
    private $handler;

    public function setUp()
    {
        $this->io = new NullIO();
        $this->permissionSetter = Mockery::mock('Meteor\Permissions\PermissionSetter', [
            'setPermissions' => null,
        ]);
        $this->handler = new SetPermissionsHandler($this->io, $this->permissionSetter);
    }

    public function testSetsPermissions()
    {
        $this->permissionSetter->shouldReceive('setPermissions')
            ->with('source', 'target')
            ->once();

        $this->handler->handle(new SetPermissions('source', 'target'), []);
    }
}
