<?php

namespace Meteor\IO\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class IOExtensionTest extends ExtensionTestCase
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
            IOExtension::SERVICE_IO,
        ];
    }
}
