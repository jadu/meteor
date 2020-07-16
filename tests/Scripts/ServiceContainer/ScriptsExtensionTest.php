<?php

namespace Meteor\Scripts\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class ScriptsExtensionTest extends ExtensionTestCase
{
    public function testServicesCanBeInstantiated()
    {
        $container = $this->loadContainer([]);

        foreach ($this->getServiceIds() as $serviceId) {
            $container->get($serviceId);
        }
    }

    private function getServiceIds()
    {
        return [
            ScriptsExtension::SERVICE_EVENT_LISTENER,
            ScriptsExtension::SERVICE_SCRIPT_RUNNER,
        ];
    }

    public function testConfigAllowsSingleCommands()
    {
        $config = $this->processConfiguration([
            'scripts' => [
                'test' => 'script',
            ],
        ]);

        $this->assertArraySubset([
            'scripts' => [
                'test' => ['script'],
            ],
        ], $config);
    }

    public function testConfigAllowsGroupedCommands()
    {
        $config = $this->processConfiguration([
            'scripts' => [
                'test' => ['script1', 'script2'],
            ],
        ]);

        $this->assertArraySubset([
            'scripts' => [
                'test' => ['script1', 'script2'],
            ],
        ], $config);
    }

    public function testConfigDoesNotNormalizeKeys()
    {
        $config = $this->processConfiguration([
            'scripts' => [
                'pre.patch.apply' => ['script'],
            ],
        ]);

        $this->assertArraySubset([
            'scripts' => [
                'pre.patch.apply' => ['script'],
            ],
        ], $config);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Circular reference detected in "test" to "test"
     */
    public function testConfigPreventsInfiniteRecursion()
    {
        $this->processConfiguration([
            'scripts' => [
                'test' => ['@test'],
            ],
        ]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Circular reference detected in "test2" to "test1"
     */
    public function testConfigPreventsInfiniteRecursionWithScriptReferences()
    {
        $this->processConfiguration([
            'scripts' => [
                'test1' => ['@test2'],
                'test2' => ['@test1'],
            ],
        ]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Circular reference detected in "test5" to "test1"
     */
    public function testConfigPreventsInfiniteRecursionWithDeepScriptReferences()
    {
        $this->processConfiguration([
            'scripts' => [
                'test1' => ['@test2'],
                'test2' => ['@test3'],
                'test3' => ['@test4'],
                'test4' => ['@test5'],
                'test5' => ['@test1'],
            ],
        ]);
    }

    public function testConfigIgnoresMultipleReferencesWhenThereIsntCircularReference()
    {
        $this->processConfiguration([
            'scripts' => [
                'patch.pre-apply' => ['@test1'],
                'patch.post-apply' => ['@test1'],
                'patch.pre-rollback' => ['@test1'],
                'patch.post-rollback' => ['@test1'],
                'test1' => ['test command' , '@test2'],
                'test2' => ['test 2 command']
            ],
        ]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testConfigCannotContainMultiDimentionalScripts()
    {
        $this->processConfiguration([
            'scripts' => [
                'test' => [
                    ['test'],
                ],
            ],
        ]);
    }

    public function testLoadingMultipleConfigsWithReferencedScriptsDoesNotCauseCircularReferenceExceptionToBeThrown()
    {
        $this->processConfiguration([
            'scripts' => [
                'patch' => ['@test'],
                'test' => ['test'],
            ],
        ]);

        $this->processConfiguration([
            'scripts' => [
                'patch' => ['@test'],
                'test' => ['test'],
            ],
        ]);
    }
}
