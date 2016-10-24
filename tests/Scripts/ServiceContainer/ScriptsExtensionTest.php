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
     * @expectedExceptionMessage Circular reference detected in "test" to "test"
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
     * @expectedExceptionMessage Circular reference detected in "test2" to "test1"
     */
    public function testConfigPreventsInfiniteRecursionWithScriptReferences()
    {
        $this->processConfiguration(array(
            'scripts' => array(
                'test1' => array('@test2'),
                'test2' => array('@test1'),
            ),
        ));
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Circular reference detected in "test5" to "test1"
     */
    public function testConfigPreventsInfiniteRecursionWithDeepScriptReferences()
    {
        $this->processConfiguration(array(
            'scripts' => array(
                'test1' => array('@test2'),
                'test2' => array('@test3'),
                'test3' => array('@test4'),
                'test4' => array('@test5'),
                'test5' => array('@test1'),
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

    public function testLoadingMultipleConfigsWithReferencedScriptsDoesNotCauseCircularReferenceExceptionToBeThrown()
    {
        $this->processConfiguration(array(
            'scripts' => array(
                'patch' => array('@test'),
                'test' => array('test'),
            ),
        ));

        $this->processConfiguration(array(
            'scripts' => array(
                'patch' => array('@test'),
                'test' => array('test'),
            ),
        ));
    }
}
