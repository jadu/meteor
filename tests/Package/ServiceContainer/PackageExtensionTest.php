<?php

namespace Meteor\Package\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class PackageExtensionTest extends ExtensionTestCase
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
            PackageExtension::SERVICE_COMBINED_PACKAGE_DEPENDENCY_CHECKER,
            PackageExtension::SERVICE_COMBINED_PACKAGE_COMBINER,
            PackageExtension::SERVICE_COMBINED_PACKAGE_RESOLVER,
            PackageExtension::SERVICE_COMMAND_PACKAGE,
            PackageExtension::SERVICE_COMPOSER_DEPENDENCY_CHECKER,
            PackageExtension::SERVICE_MIGRATIONS_COPIER,
            PackageExtension::SERVICE_PACKAGE_ARCHIVER,
            PackageExtension::SERVICE_PACKAGE_CREATOR,
            PackageExtension::SERVICE_PACKAGE_EXTRACTOR,
            PackageExtension::SERVICE_PACKAGE_NAME_RESOLVER,
        ];
    }

    public function testCombineConfigSectionCanHaveKeys()
    {
        $config = $this->processConfiguration([
            'package' => [
                'combine' => [
                    'jadu/cms' => '13.6.0',
                    'jadu/xfp' => '3.7.1',
                ],
            ],
        ]);

        $this->assertSame([
            'jadu/cms' => '13.6.0',
            'jadu/xfp' => '3.7.1',
        ], $config['package']['combine']);
    }

    public function testComposerConfigSectionCanHaveKeys()
    {
        $config = $this->processConfiguration([
            'package' => [
                'composer' => [
                    'jadu/cms-dependencies' => '~13.6.0',
                    'jadu/xfp-dependencies' => '~3.7.1',
                ],
            ],
        ]);

        $this->assertSame([
            'jadu/cms-dependencies' => '~13.6.0',
            'jadu/xfp-dependencies' => '~3.7.1',
        ], $config['package']['composer']);
    }
}
