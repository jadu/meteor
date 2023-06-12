<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use Mockery;
use PHPUnit\Framework\TestCase;

class SetPermissionsHandlerTest extends TestCase
{
    private $io;
    private $permissionSetter;
    private $handler;

    protected function setUp(): void
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
