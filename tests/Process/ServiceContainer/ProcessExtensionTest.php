<?php

namespace Meteor\Process\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class ProcessExtensionTest extends ExtensionTestCase
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
            ProcessExtension::SERVICE_PROCESS_RUNNER,
        ];
    }
}
