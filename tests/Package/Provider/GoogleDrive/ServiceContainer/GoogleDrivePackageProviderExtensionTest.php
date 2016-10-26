<?php

namespace Meteor\Patch\Strategy\Overwrite\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class GoogleDrivePackageProviderExtensionTest extends ExtensionTestCase
{
    public function testServicesLoadedWhenGDriveProvider()
    {
        $container = $this->loadContainer([
            'package' => [
                'provider' => 'gdrive',
            ],
        ]);

        $this->assertTrue($container->has('package.provider.gdrive'));

        $container->get('package.provider.gdrive');
    }

    public function testServicesNotLoadedWhenNotGDriveProvider()
    {
        $container = $this->loadContainer([
            'package' => [
                'provider' => 'dummy',
            ],
        ]);

        $this->assertFalse($container->has('package.provider.gdrive'));
    }

    public function testAddsDefaultFolders()
    {
        $config = $this->processConfiguration([]);

        $this->assertArraySubset([
            'folders' => [
                'jadu/cms' => '0B3tlQeNsllCKY2tzbFpUUkI2OGM',
                'jadu/xfp' => '0B2h2-RgE2WidOHRhZVNUbUc1Z0E',
            ],
        ], $config['gdrive_package_provider']);
    }
}
