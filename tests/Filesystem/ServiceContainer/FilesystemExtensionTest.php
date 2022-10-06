<?php

namespace Meteor\Filesystem\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class FilesystemExtensionTest extends ExtensionTestCase
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
            FilesystemExtension::SERVICE_FILESYSTEM,
            FilesystemExtension::SERVICE_FINDER_FACTORY,
        ];
    }
}
