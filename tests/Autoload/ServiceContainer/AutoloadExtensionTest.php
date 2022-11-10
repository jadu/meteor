<?php

namespace Meteor\Autoload\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;
use org\bovigo\vfs\vfsStream;
use RuntimeException;

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

        static::assertArrayHasKey('Test_', $prefixes);
        static::assertEquals([vfsStream::url('root/to_patch/vendor/test/test/src/')], $prefixes['Test_']);
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

        static::assertArrayHasKey('Test\\', $prefixes);
        static::assertEquals([vfsStream::url('root/to_patch/vendor/test/test/src/')], $prefixes['Test\\']);
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

        static::assertTrue(in_array(vfsStream::url('root/to_patch/vendor/test/test/file.php'), $classMap));
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

        static::assertArrayHasKey('Test_', $prefixes);
        static::assertEquals([vfsStream::url('root/vendor/test/test/src/')], $prefixes['Test_']);
    }

    public function testThrowsExceptionWhenComposerPackageNotFound()
    {
        $this->expectException(RuntimeException::class);
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

        static::assertArrayHasKey('Jadu\\', $prefixes);
        static::assertEquals(['/path/to/working/src/'], $prefixes['Jadu\\']);
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

        static::assertArrayHasKey('Jadu\\', $prefixes);
        static::assertEquals(['/path/to/working/src/', '/path/to/working/tests/'], $prefixes['Jadu\\']);
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

        static::assertSame([
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

        static::assertSame([
            'Jadu\\' => ['src/'],
        ], $config['autoload']['psr-4']);
    }
}
