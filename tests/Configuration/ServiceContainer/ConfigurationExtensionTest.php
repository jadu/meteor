<?php

namespace Meteor\Configuration\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class ConfigurationExtensionTest extends ExtensionTestCase
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
            ConfigurationExtension::SERVICE_WRITER,
        );
    }
}
