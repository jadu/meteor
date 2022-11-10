<?php

namespace Meteor\Configuration;

use Meteor\Configuration\Exception\ConfigurationLoadingException;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Mockery;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationLoaderTest extends TestCase
{
    private $extensionManager;
    private $treeBuilder;
    private $processor;
    private $configurationLoader;

    protected function setUp(): void
    {
        $this->extensionManager = Mockery::mock(ExtensionManager::class, [
            'getExtensions' => [],
        ]);
        $this->treeBuilder = new TreeBuilder('meteor');
        $this->processor = new Processor();
        $this->configurationLoader = new ConfigurationLoader($this->extensionManager, $this->treeBuilder, $this->processor);
    }

    public function testBuildTreeConfiguresExtensions()
    {
        $extension = Mockery::mock(ExtensionInterface::class, [
            'getConfigKey' => 'test',
        ]);

        // NB: Twice due to building the combined package tree as well
        $extension->shouldReceive('configure')
            ->twice();

        $this->configurationLoader->buildTree([$extension]);

        $config = $this->configurationLoader->process([
            'name' => 'jadu/xfp',
        ]);

        static::assertArrayHasKey('extensions', $config);
    }

    public function testAllowsNameSection()
    {
        $this->configurationLoader->buildTree([]);

        $config = $this->configurationLoader->process([
            'name' => 'jadu/xfp',
        ]);

        static::assertArrayHasKey('name', $config);
        static::assertEquals('jadu/xfp', $config['name']);
    }

    public function testGeneratesAUniqueNameIfNotProvided()
    {
        $this->configurationLoader->buildTree([]);

        $config = $this->configurationLoader->process([]);

        static::assertArrayHasKey('name', $config);
        static::assertNotEmpty($config['name']);
    }

    public function testAllowsExtensionsSection()
    {
        $this->configurationLoader->buildTree([]);

        $config = $this->configurationLoader->process([
            'extensions' => [
                'Meteor\Test\ServiceContainer\TestExtension',
            ],
        ]);

        static::assertArrayHasKey('extensions', $config);
        static::assertEquals(['Meteor\Test\ServiceContainer\TestExtension'], $config['extensions']);
    }

    public function testParseReturnsConfigAsArray()
    {
        $json = <<<'JSON'
{
    "name": "jadu/xfp",
    "migrations": {
        "table": "JaduMigrationsXFP"
    }
}
JSON;

        vfsStream::setup('root', null, [
            'meteor.json' => $json,
        ]);

        $config = $this->configurationLoader->parse(vfsStream::url('root/meteor.json'));

        static::assertSame([
            'name' => 'jadu/xfp',
            'migrations' => [
                'table' => 'JaduMigrationsXFP',
            ],
        ], $config);
    }

    public function testParseThrowsExceptionWhenFileCannotBeRead()
    {
        $this->expectException(ConfigurationLoadingException::class);

        vfsStream::setup('root');

        $this->configurationLoader->parse(vfsStream::url('root/meteor.json'));
    }

    public function testParseThrowsExceptionWhenJsonCannotBeParsed()
    {
        $this->expectException(ConfigurationLoadingException::class);

        vfsStream::setup('root', null, [
            'meteor.json' => '!!!',
        ]);

        $this->configurationLoader->parse(vfsStream::url('root/meteor.json'));
    }

    public function testLoad()
    {
        $json = <<<'JSON'
{
    "name": "jadu/xfp"
}
JSON;

        vfsStream::setup('root', null, [
            'meteor.json' => $json,
        ]);

        $this->configurationLoader->buildTree([]);
        $config = $this->configurationLoader->load(vfsStream::url('root'));

        static::assertSame([
            'name' => 'jadu/xfp',
            'extensions' => [],
            'combined' => [],
        ], $config);
    }

    public function testLoadCombined()
    {
        $json = <<<'JSON'
{
    "name": "jadu/xfp",
    "combined": [
        {
            "name": "jadu/cms"
        }
    ]
}
JSON;

        vfsStream::setup('root', null, [
            'meteor.json' => $json,
        ]);

        $this->configurationLoader->buildTree([]);
        $config = $this->configurationLoader->load(vfsStream::url('root'));

        static::assertSame([
            'name' => 'jadu/xfp',
            'combined' => [
                [
                    'name' => 'jadu/cms',
                    'extensions' => [],
                ],
            ],
            'extensions' => [],
        ], $config);
    }

    public function testLoadIgnoresUnrecognisedOptionsWhenNotStrict()
    {
        $json = <<<'JSON'
{
    "name": "jadu/xfp",
    "test_unrecognised": true
}
JSON;

        vfsStream::setup('root', null, [
            'meteor.json' => $json,
        ]);

        $this->configurationLoader->buildTree([]);
        $config = $this->configurationLoader->load(vfsStream::url('root'), false);

        static::assertSame([
            'name' => 'jadu/xfp',
            'extensions' => [],
            'combined' => [],
        ], $config);
    }

    public function testCannotProcessBeforeTreeIsBuild()
    {
        $this->expectException(ConfigurationLoadingException::class);

        $this->configurationLoader->process([]);
    }
}
