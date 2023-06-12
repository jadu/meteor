<?php

namespace Meteor\Scripts\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ScriptsExtensionTest extends ExtensionTestCase
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

        static::assertArrayHasKey('scripts', $config);
        static::assertEquals(['test' => ['script']], $config['scripts']);
    }

    public function testConfigAllowsGroupedCommands()
    {
        $config = $this->processConfiguration([
            'scripts' => [
                'test' => ['script1', 'script2'],
            ],
        ]);

        static::assertArrayHasKey('scripts', $config);
        static::assertEquals(['test' => ['script1', 'script2']], $config['scripts']);
    }

    public function testConfigDoesNotNormalizeKeys()
    {
        $config = $this->processConfiguration([
            'scripts' => [
                'pre.patch.apply' => ['script'],
            ],
        ]);

        static::assertArrayHasKey('scripts', $config);
        static::assertEquals(['pre.patch.apply' => ['script']], $config['scripts']);
    }

    public function testConfigPreventsInfiniteRecursion()
    {
        static::expectException(InvalidConfigurationException::class);
        static::expectExceptionMessage('Circular reference detected in "test" to "test"');

        $this->processConfiguration([
            'scripts' => [
                'test' => ['@test'],
            ],
        ]);
    }

    public function testConfigPreventsInfiniteRecursionWithScriptReferences()
    {
        static::expectException(InvalidConfigurationException::class);
        static::expectExceptionMessage('Circular reference detected in "test2" to "test1"');

        $this->processConfiguration([
            'scripts' => [
                'test1' => ['@test2'],
                'test2' => ['@test1'],
            ],
        ]);
    }

    public function testConfigPreventsInfiniteRecursionWithDeepScriptReferences()
    {
        static::expectException(InvalidConfigurationException::class);
        static::expectExceptionMessage('Circular reference detected in "test5" to "test1"');

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
                'test1' => ['test command', '@test2'],
                'test2' => ['test 2 command'],
            ],
        ]);
    }

    public function testConfigCannotContainMultiDimentionalScripts()
    {
        static::expectException(InvalidConfigurationException::class);

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
