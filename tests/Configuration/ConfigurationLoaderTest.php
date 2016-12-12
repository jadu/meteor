<?php

namespace Meteor\Configuration;

use Mockery;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $extensionManager;
    private $treeBuilder;
    private $processor;
    private $configurationLoader;

    public function setUp()
    {
        $this->extensionManager = Mockery::mock('Meteor\ServiceContainer\ExtensionManager', [
            'getExtensions' => [],
        ]);
        $this->treeBuilder = new TreeBuilder();
        $this->processor = new Processor();
        $this->configurationLoader = new ConfigurationLoader($this->extensionManager, $this->treeBuilder, $this->processor);
    }

    public function testBuildTreeConfiguresExtensions()
    {
        $extension = Mockery::mock('Meteor\ServiceContainer\ExtensionInterface', [
            'getConfigKey' => 'test',
        ]);

        // NB: Twice due to building the combined package tree as well
        $extension->shouldReceive('configure')
            ->twice();

        $this->configurationLoader->buildTree([$extension]);
    }

    public function testAllowsNameSection()
    {
        $this->configurationLoader->buildTree([]);

        $config = $this->configurationLoader->process([
            'name' => 'jadu/xfp',
        ]);

        $this->assertArraySubset([
            'name' => 'jadu/xfp',
        ], $config);
    }

    public function testGeneratesAUniqueNameIfNotProvided()
    {
        $this->configurationLoader->buildTree([]);

        $config = $this->configurationLoader->process([]);

        $this->assertArrayHasKey('name', $config);
        $this->assertNotEmpty($config['name']);
    }

    public function testAllowsExtensionsSection()
    {
        $this->configurationLoader->buildTree([]);

        $config = $this->configurationLoader->process([
            'extensions' => [
                'Meteor\Test\ServiceContainer\TestExtension',
            ],
        ]);

        $this->assertArraySubset([
            'extensions' => [
                'Meteor\Test\ServiceContainer\TestExtension',
            ],
        ], $config);
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

        $this->assertSame([
            'name' => 'jadu/xfp',
            'migrations' => [
                'table' => 'JaduMigrationsXFP',
            ],
        ], $config);
    }

    /**
     * @expectedException \Meteor\Configuration\Exception\ConfigurationLoadingException
     */
    public function testParseThrowsExceptionWhenFileCannotBeRead()
    {
        vfsStream::setup('root');

        $this->configurationLoader->parse(vfsStream::url('root/meteor.json'));
    }

    /**
     * @expectedException \Meteor\Configuration\Exception\ConfigurationLoadingException
     */
    public function testParseThrowsExceptionWhenJsonCannotBeParsed()
    {
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

        $this->assertSame([
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

        $this->assertSame([
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

        $this->assertSame([
            'name' => 'jadu/xfp',
            'extensions' => [],
            'combined' => [],
        ], $config);
    }

    /**
     * @expectedException \Meteor\Configuration\Exception\ConfigurationLoadingException
     */
    public function testCannotProcessBeforeTreeIsBuild()
    {
        $this->configurationLoader->process([]);
    }
}
