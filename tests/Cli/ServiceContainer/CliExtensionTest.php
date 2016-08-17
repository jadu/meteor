<?php

namespace Meteor\Cli\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class CliExtensionTest extends ExtensionTestCase
{
    public function testHasCommandServiceIdsParameter()
    {
        $container = $this->loadContainer(array());

        $this->assertTrue($container->hasParameter(CliExtension::PARAMETER_COMMAND_SERVICE_IDS));
    }
}
