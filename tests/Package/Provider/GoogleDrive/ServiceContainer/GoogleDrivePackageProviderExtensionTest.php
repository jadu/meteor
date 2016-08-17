<?php

namespace Meteor\Patch\Strategy\Overwrite\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class GoogleDrivePackageProviderExtensionTest extends ExtensionTestCase
{
    public function testServicesLoadedWhenGDriveProvider()
    {
        $container = $this->loadContainer(array(
            'package' => array(
                'provider' => 'gdrive',
            ),
        ));

        $this->assertTrue($container->has('package.provider.gdrive'));

        $container->get('package.provider.gdrive');
    }

    public function testServicesNotLoadedWhenNotGDriveProvider()
    {
        $container = $this->loadContainer(array(
            'package' => array(
                'provider' => 'dummy',
            ),
        ));

        $this->assertFalse($container->has('package.provider.gdrive'));
    }

    public function testAddsDefaultFolders()
    {
        $config = $this->processConfiguration(array());

        $this->assertArraySubset(array(
            'folders' => array(
                'jadu/cms' => '0B3tlQeNsllCKY2tzbFpUUkI2OGM',
                'jadu/xfp' => '0B2h2-RgE2WidOHRhZVNUbUc1Z0E',
            ),
        ), $config['gdrive_package_provider']);
    }
}
