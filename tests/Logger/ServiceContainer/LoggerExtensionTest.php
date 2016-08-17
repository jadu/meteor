<?php

namespace Meteor\Logger\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class LoggerExtensionTest extends ExtensionTestCase
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
            LoggerExtension::SERVICE_LOGGER,
        );
    }
}
