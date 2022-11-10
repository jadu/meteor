<?php

namespace Meteor\Package;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PackageNameResolverTest extends TestCase
{
    private $resolver;

    protected function setUp(): void
    {
        $this->resolver = new PackageNameResolver();

        vfsStream::setup('root');
    }

    public function testResolveReturnsFileNameWhenGiven()
    {
        $config = [
            'name' => 'jadu/test',
        ];

        static::assertSame('package', $this->resolver->resolve('package', vfsStream::url('root'), $config));
    }

    /**
     * @dataProvider packageNameProvider
     */
    public function testResolveGeneratesFileNameFromPackageName($packageName, $expectedFileName)
    {
        $config = [
            'name' => $packageName,
        ];

        static::assertSame($expectedFileName, $this->resolver->resolve('', vfsStream::url('root'), $config));
    }

    public function packageNameProvider()
    {
        return [
            ['jadu/test', 'jadu_test'],
            ['XFP-3.9.1', 'XFP-3.9.1'],
        ];
    }

    /**
     * @dataProvider versionProvider
     */
    public function testResolveGeneratesFileNameFromPackageNameAndVersion($version, $expectedFileName)
    {
        $config = [
            'name' => 'jadu/test',
            'package' => [
                'version' => 'VERSION',
            ],
        ];

        vfsStream::setup('root', null, [
            'VERSION' => $version,
        ]);

        static::assertSame($expectedFileName, $this->resolver->resolve('', vfsStream::url('root'), $config));
    }

    public function versionProvider()
    {
        return [
            ['1.0.0', 'jadu_test_1.0.0'],
            ['1.2.0-9298a2a08a460f2e3c16a71bb01d472af07137ba', 'jadu_test_1.2.0-9298a2a08a460f2e3c16a71bb01d472af07137ba'],
            ['$$$$', 'jadu_test'],
        ];
    }

    public function testResolveThrowsExceptionWhenVersionFileCannotBeFound()
    {
        static::expectException(RuntimeException::class);

        $config = [
            'name' => 'jadu/test',
            'package' => [
                'version' => 'VERSION',
            ],
        ];

        $this->resolver->resolve('', vfsStream::url('root'), $config);
    }

    /**
     * @dataProvider invalidFileNameProvider
     */
    public function testResolveGeneratesFileNameFromPackageNameWhenFileNameInvalid($fileName)
    {
        $config = [
            'name' => 'jadu/test',
        ];

        static::assertSame('jadu_test', $this->resolver->resolve($fileName, vfsStream::url('root'), $config));
    }

    public function invalidFileNameProvider()
    {
        return [
            [''],
            ['     '],
            ['___'],
            ['package$$$'],
        ];
    }
}
