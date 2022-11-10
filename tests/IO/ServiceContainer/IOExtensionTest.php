<?php

namespace Meteor\IO\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class IOExtensionTest extends ExtensionTestCase
{
    public function testServicesCanBeInstantiated()
    {
        $container = $this->loadContainer([]);

        foreach ($this->getServiceIds() as $serviceId) {
            static::assertTrue($container->has($serviceId), sprintf('Container has "%s" service', $serviceId));
        }
    }

    private function getServiceIds()
    {
        return [
            IOExtension::SERVICE_IO,
        ];
    }
}
