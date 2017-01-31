<?php

namespace Meteor\Autoload\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;
use org\bovigo\vfs\vfsStream;

class AutoloadExtensionTest extends ExtensionTestCase
{
    public function testAddsComposerPackagesWithPsr0()
    {
        $composerJson = <<<'JSON'
{
    "name": "test/test",
    "autoload": {
        "psr-0": {
            "Test_": "src/"
        }
    }
}
JSON;

        vfsStream::setup('root', null, [
            'to_patch' => [
                'vendor' => [
                    'test' => [
                        'test' => [
                            'composer.json' => $composerJson,
                        ],
                    ],
                ],
            ],
        ]);

        $container = $this->loadContainer([
            'autoload' => [
                'composer' => ['test/test'],
            ],
        ], vfsStream::url('root'));

        $prefixes = $container->get(AutoloadExtension::SERVICE_CLASS_LOADER)->getPrefixes();

        $this->assertArraySubset([
            'Test_' => [vfsStream::url('root/to_patch/vendor/test/test/src/')],
        ], $prefixes);
    }

    public function testAddsComposerPackagesWithPsr4()
    {
        $composerJson = <<<'JSON'
{
    "name": "test/test",
    "autoload": {
        "psr-4": {
            "Test\\": "src/"
        }
    }
}
JSON;

        vfsStream::setup('root', null, [
            'to_patch' => [
                'vendor' => [
                    'test' => [
                        'test' => [
                            'composer.json' => $composerJson,
                        ],
                    ],
                ],
            ],
        ]);

        $container = $this->loadContainer([
            'autoload' => [
                'composer' => ['test/test'],
            ],
        ], vfsStream::url('root'));

        $prefixes = $container->get(AutoloadExtension::SERVICE_CLASS_LOADER)->getPrefixesPsr4();

        $this->assertArraySubset([
            'Test\\' => [vfsStream::url('root/to_patch/vendor/test/test/src/')],
        ], $prefixes);
    }

    public function testAddsComposerPackagesWithClassMap()
    {
        $composerJson = <<<'JSON'
{
    "name": "test/test",
    "autoload": {
        "classmap": ["file.php"]
    }
}
JSON;

        vfsStream::setup('root', null, [
            'to_patch' => [
                'vendor' => [
                    'test' => [
                        'test' => [
                            'composer.json' => $composerJson,
                        ],
                    ],
                ],
            ],
        ]);

        $container = $this->loadContainer([
            'autoload' => [
                'composer' => ['test/test'],
            ],
        ], vfsStream::url('root'));

        $classMap = $container->get(AutoloadExtension::SERVICE_CLASS_LOADER)->getClassMap();

        $this->assertArraySubset([vfsStream::url('root/to_patch/vendor/test/test/file.php')], $classMap);
    }

    public function testAddsComposerPackagesFromRootVendor()
    {
        $composerJson = <<<'JSON'
{
    "name": "test/test",
    "autoload": {
        "psr-0": {
            "Test_": "src/"
        }
    }
}
JSON;

        vfsStream::setup('root', null, [
            'vendor' => [
                'test' => [
                    'test' => [
                        'composer.json' => $composerJson,
                    ],
                ],
            ],
        ]);

        $container = $this->loadContainer([
            'autoload' => [
                'composer' => ['test/test'],
            ],
        ], vfsStream::url('root'));

        $prefixes = $container->get(AutoloadExtension::SERVICE_CLASS_LOADER)->getPrefixes();

        $this->assertArraySubset([
            'Test_' => [vfsStream::url('root/vendor/test/test/src/')],
        ], $prefixes);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testThrowsExceptionWhenComposerPackageNotFound()
    {
        vfsStream::setup('root');

        $container = $this->loadContainer([
            'autoload' => [
                'composer' => ['test/test'],
            ],
        ], vfsStream::url('root'));
    }

    public function testAddsPsr4Path()
    {
        $container = $this->loadContainer([
            'autoload' => [
                'psr-4' => [
                    'Jadu\\' => 'src/',
                ],
            ],
        ], '/path/to/working');

        $prefixes = $container->get(AutoloadExtension::SERVICE_CLASS_LOADER)->getPrefixesPsr4();

        $this->assertArraySubset([
            'Jadu\\' => ['/path/to/working/src/'],
        ], $prefixes);
    }

    public function testAddsPsr4Paths()
    {
        $container = $this->loadContainer([
            'autoload' => [
                'psr-4' => [
                    'Jadu\\' => ['src/', 'tests/'],
                ],
            ],
        ], '/path/to/working');

        $prefixes = $container->get(AutoloadExtension::SERVICE_CLASS_LOADER)->getPrefixesPsr4();

        $this->assertArraySubset([
            'Jadu\\' => ['/path/to/working/src/', '/path/to/working/tests/'],
        ], $prefixes);
    }

    public function testAutoloadComposerPackages()
    {
        $config = $this->processConfiguration([
            'autoload' => [
                'composer' => [
                    'spacecraft/migrations',
                ],
            ],
        ]);

        $this->assertSame([
            'spacecraft/migrations',
        ], $config['autoload']['composer']);
    }

    public function testAutoloadPsr4Paths()
    {
        $config = $this->processConfiguration([
            'autoload' => [
                'psr-4' => [
                    'Jadu\\' => ['src/'],
                ],
            ],
        ]);

        $this->assertSame([
            'Jadu\\' => ['src/'],
        ], $config['autoload']['psr-4']);
    }
}
