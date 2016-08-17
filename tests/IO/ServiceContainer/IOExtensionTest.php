<?php

namespace Meteor\IO\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class IOExtensionTest extends ExtensionTestCase
{
    public function testServicesCanBeInstantiated()
    {
        $container = $this->loadContainer(array());

        foreach ($this->getServiceIds() as $serviceId) {
            $container->get($serviceId);
        }
    }

    private function getServiceIds()
    {
        return array(
            IOExtension::SERVICE_IO,
        );
    }
}
