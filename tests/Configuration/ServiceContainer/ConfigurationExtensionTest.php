<?php

namespace Meteor\Configuration\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class ConfigurationExtensionTest extends ExtensionTestCase
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
            ConfigurationExtension::SERVICE_WRITER,
        ];
    }
}
