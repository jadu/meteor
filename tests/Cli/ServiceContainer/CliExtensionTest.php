<?php

namespace Meteor\Cli\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class CliExtensionTest extends ExtensionTestCase
{
    public function testHasCommandServiceIdsParameter()
    {
        $container = $this->loadContainer([]);

        static::assertTrue($container->hasParameter(CliExtension::PARAMETER_COMMAND_SERVICE_IDS));
    }
}
