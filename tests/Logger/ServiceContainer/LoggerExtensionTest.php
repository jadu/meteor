<?php

namespace Meteor\Logger\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class LoggerExtensionTest extends ExtensionTestCase
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
            LoggerExtension::SERVICE_LOGGER,
        ];
    }
}
