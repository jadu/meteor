<?php

namespace Meteor\Scripts\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class ScriptsExtensionTest extends ExtensionTestCase
{
    public function testServicesCanBeInstantiated()
    {
        $container = $this->loadContainer(array());

        foreach ($this->getServiceIds() as $serviceId) {
            $container->get($serviceId);
        }
    }

    private function getServiceIds()
    {
        return array(
            ScriptsExtension::SERVICE_EVENT_LISTENER,
            ScriptsExtension::SERVICE_SCRIPT_RUNNER,
        );
    }

    public function testConfigAllowsSingleCommands()
    {
        $config = $this->processConfiguration(array(
            'scripts' => array(
                'test' => 'script',
            ),
        ));

        $this->assertArraySubset(array(
            'scripts' => array(
                'test' => array('script'),
            ),
        ), $config);
    }

    public function testConfigAllowsGroupedCommands()
    {
        $config = $this->processConfiguration(array(
            'scripts' => array(
                'test' => array('script1', 'script2'),
            ),
        ));

        $this->assertArraySubset(array(
            'scripts' => array(
                'test' => array('script1', 'script2'),
            ),
        ), $config);
    }

    public function testConfigDoesNotNormalizeKeys()
    {
        $config = $this->processConfiguration(array(
            'scripts' => array(
                'pre.patch.apply' => array('script'),
            ),
        ));

        $this->assertArraySubset(array(
            'scripts' => array(
                'pre.patch.apply' => array('script'),
            ),
        ), $config);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testConfigPreventsInfiniteRecursion()
    {
        $this->processConfiguration(array(
            'scripts' => array(
                'test' => array('@test'),
            ),
        ));
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testConfigCannotContainMultiDimentionalScripts()
    {
        $config = $this->processConfiguration(array(
            'scripts' => array(
                'test' => array(
                    array('test'),
                ),
            ),
        ));
    }
}
