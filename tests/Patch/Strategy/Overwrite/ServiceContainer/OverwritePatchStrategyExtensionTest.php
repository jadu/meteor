<?php

namespace Meteor\Patch\Strategy\Overwrite\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class OverwritePatchStrategyExtensionTest extends ExtensionTestCase
{
    public function testServicesLoadedWhenOverwriteStrategy()
    {
        $container = $this->loadContainer([
            'patch' => [
                'strategy' => 'overwrite',
            ],
        ]);

        static::assertTrue($container->has('patch.strategy.overwrite'));
    }

    public function testServicesNotLoadedWhenNotOverwriteStrategy()
    {
        $container = $this->loadContainer([
            'patch' => [
                'strategy' => 'dummy',
            ],
        ]);

        static::assertFalse($container->has('patch.strategy.overwrite'));
    }
}
