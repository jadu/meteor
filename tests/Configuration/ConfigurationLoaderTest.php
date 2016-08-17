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
        $this->extensionManager = Mockery::mock('Meteor\ServiceContainer\ExtensionManager', array(
            'getExtensions' => array(),
        ));
        $this->treeBuilder = new TreeBuilder();
        $this->processor = new Processor();
        $this->configurationLoader = new ConfigurationLoader($this->extensionManager, $this->treeBuilder, $this->processor);
    }

    public function testBuildTreeConfiguresExtensions()
    {
        $extension = Mockery::mock('Meteor\ServiceContainer\ExtensionInterface', array(
            'getConfigKey' => 'test',
        ));

        // NB: Twice due to building the combined package tree as well
        $extension->shouldReceive('configure')
            ->twice();

        $this->configurationLoader->buildTree(array($extension));
    }

    public function testAllowsNameSection()
    {
        $this->configurationLoader->buildTree(array());

        $config = $this->configurationLoader->process(array(
            'name' => 'jadu/xfp',
        ));

        $this->assertArraySubset(array(
            'name' => 'jadu/xfp',
        ), $config);
    }

    public function testGeneratesAUniqueNameIfNotProvided()
    {
        $this->configurationLoader->buildTree(array());

        $config = $this->configurationLoader->process(array());

        $this->assertArrayHasKey('name', $config);
        $this->assertNotEmpty($config['name']);
    }

    public function testAllowsExtensionsSection()
    {
        $this->configurationLoader->buildTree(array());

        $config = $this->configurationLoader->process(array(
            'extensions' => array(
                'Meteor\Test\ServiceContainer\TestExtension',
            ),
        ));

        $this->assertArraySubset(array(
            'extensions' => array(
                'Meteor\Test\ServiceContainer\TestExtension',
            ),
        ), $config);
    }

    public function testParseReturnsConfigAsArray()
    {
        $json = <<<JSON
{
    "name": "jadu/xfp",
    "migrations": {
        "table": "JaduMigrationsXFP"
    }
}
JSON;

        vfsStream::setup('root', null, array(
            'meteor.json' => $json,
        ));

        $config = $this->configurationLoader->parse(vfsStream::url('root/meteor.json'));

        $this->assertSame(array(
            'name' => 'jadu/xfp',
            'migrations' => array(
                'table' => 'JaduMigrationsXFP',
            ),
        ), $config);
    }

    /**
     * @expectedException Meteor\Configuration\Exception\ConfigurationLoadingException
     */
    public function testParseThrowsExceptionWhenFileCannotBeRead()
    {
        vfsStream::setup('root');

        $this->configurationLoader->parse(vfsStream::url('root/meteor.json'));
    }

    /**
     * @expectedException Meteor\Configuration\Exception\ConfigurationLoadingException
     */
    public function testParseThrowsExceptionWhenJsonCannotBeParsed()
    {
        vfsStream::setup('root', null, array(
            'meteor.json' => '!!!',
        ));

        $this->configurationLoader->parse(vfsStream::url('root/meteor.json'));
    }

    public function testLoad()
    {
        $json = <<<JSON
{
    "name": "jadu/xfp"
}
JSON;

        vfsStream::setup('root', null, array(
            'meteor.json' => $json,
        ));

        $this->configurationLoader->buildTree(array());
        $config = $this->configurationLoader->load(vfsStream::url('root'));

        $this->assertSame(array(
            'name' => 'jadu/xfp',
            'extensions' => array(),
            'combined' => array(),
        ), $config);
    }

    public function testLoadIgnoresUnrecognisedOptionsWhenNotStrict()
    {
        $json = <<<JSON
{
    "name": "jadu/xfp",
    "test_unrecognised": true
}
JSON;

        vfsStream::setup('root', null, array(
            'meteor.json' => $json,
        ));

        $this->configurationLoader->buildTree(array());
        $config = $this->configurationLoader->load(vfsStream::url('root'), false);

        $this->assertSame(array(
            'name' => 'jadu/xfp',
            'extensions' => array(),
            'combined' => array(),
        ), $config);
    }

    /**
     * @expectedException Meteor\Configuration\Exception\ConfigurationLoadingException
     */
    public function testCannotProcessBeforeTreeIsBuild()
    {
        $this->configurationLoader->process(array());
    }
}
