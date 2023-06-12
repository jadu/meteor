<?php

namespace Meteor\Platform\ServiceContainer;

use Meteor\Platform\Windows\WindowsPlatform;
use Meteor\ServiceContainer\ExtensionTestCase;

class PlatformExtensionTest extends ExtensionTestCase
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
            PlatformExtension::SERVICE_PLATFORM_UNIX,
            PlatformExtension::SERVICE_PLATFORM_WINDOWS,
            PlatformExtension::SERVICE_UNIX_INSTALL_CONFIG_LOADER,
        ];
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetsPlatformAliasCorrectlyOnWindows()
    {
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            define('PHP_WINDOWS_VERSION_BUILD', 1);
        }

        $container = $this->loadContainer([]);

        static::assertInstanceOf(
            WindowsPlatform::class,
            $container->get(PlatformExtension::SERVICE_PLATFORM)
        );
    }
}
