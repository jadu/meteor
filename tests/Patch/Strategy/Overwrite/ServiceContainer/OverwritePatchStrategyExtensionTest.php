<?php

namespace Meteor\Patch\Strategy\Overwrite\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class OverwritePatchStrategyExtensionTest extends ExtensionTestCase
{
    public function testServicesLoadedWhenOverwriteStrategy()
    {
        $container = $this->loadContainer(array(
            'patch' => array(
                'strategy' => 'overwrite',
            ),
        ));

        $this->assertTrue($container->has('patch.strategy.overwrite'));
    }

    public function testServicesNotLoadedWhenNotOverwriteStrategy()
    {
        $container = $this->loadContainer(array(
            'patch' => array(
                'strategy' => 'dummy',
            ),
        ));

        $this->assertFalse($container->has('patch.strategy.overwrite'));
    }
}
