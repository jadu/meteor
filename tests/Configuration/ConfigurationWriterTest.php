<?php

namespace Meteor\Configuration;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ConfigurationWriterTest extends TestCase
{
    private $configurationWriter;

    protected function setUp(): void
    {
        $this->configurationWriter = new ConfigurationWriter();

        vfsStream::setup('root');
    }

    public function testWritesJsonFileToGivenPath()
    {
        $path = vfsStream::url('root/meteor.package.json');
        $config = [
            'name' => 'jadu/xfp',
            'package' => [
                'files' => [
                    '/**',
                ],
            ],
        ];

        $expectedJson = <<<'JSON'
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

        static::assertTrue(file_exists($path));
        static::assertEquals($expectedJson, file_get_contents($path));
    }

    public function testRemovesEmptyValues()
    {
        $path = vfsStream::url('root/meteor.package.json');
        $config = [
            'name' => 'jadu/xfp',
            'package' => [],
            'migrations' => null,
        ];

        $expectedJson = <<<'JSON'
{
    "name": "jadu/xfp"
}
JSON;

        $this->configurationWriter->write($path, $config);

        static::assertTrue(file_exists($path));
        static::assertEquals($expectedJson, file_get_contents($path));
    }
}
