<?php

namespace Meteor\Permissions\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class PermissionsExtensionTest extends ExtensionTestCase
{
    public function testServicesCanBeInstantiated()
    {
        $container = $this->loadContainer([]);

        foreach ($this->getServiceIds() as $serviceId) {
            $container->get($serviceId);
        }
    }

    private function getServiceIds()
    {
        return [
            PermissionsExtension::SERVICE_COMMAND_RESET_PERMISSIONS,
            PermissionsExtension::SERVICE_PERMISSION_LOADER,
            PermissionsExtension::SERVICE_PERMISSION_SETTER,
        ];
    }
}
