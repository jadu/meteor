<?php

namespace Meteor\Configuration;

use org\bovigo\vfs\vfsStream;

class ConfigurationWriterTest extends \PHPUnit_Framework_TestCase
{
    private $configurationWriter;

    public function setUp()
    {
        $this->configurationWriter = new ConfigurationWriter();

        vfsStream::setup('root');
    }

    public function testWritesJsonFileToGivenPath()
    {
        $path = vfsStream::url('root/meteor.package.json');
        $config = array(
            'name' => 'jadu/xfp',
            'package' => array(
                'files' => array(
                    '/**',
                ),
            ),
        );

        $expectedJson = <<<JSON
{
    "name": "jadu/xfp",
    "package": {
        "files": [
            "/**"
        ]
    }
}
JSON;

        $this->configurationWriter->write($path, $config);

        $this->assertTrue(file_exists($path));
        $this->assertEquals($expectedJson, file_get_contents($path));
    }

    public function testRemovesEmptyValues()
    {
        $path = vfsStream::url('root/meteor.package.json');
        $config = array(
            'name' => 'jadu/xfp',
            'package' => array(),
            'migrations' => null,
        );

        $expectedJson = <<<JSON
{
    "name": "jadu/xfp"
}
JSON;

        $this->configurationWriter->write($path, $config);

        $this->assertTrue(file_exists($path));
        $this->assertEquals($expectedJson, file_get_contents($path));
    }
}
