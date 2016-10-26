<?php

namespace Meteor\Package;

use org\bovigo\vfs\vfsStream;

class PackageNameResolverTest extends \PHPUnit_Framework_TestCase
{
    private $resolver;

    public function setUp()
    {
        $this->resolver = new PackageNameResolver();

        vfsStream::setup('root');
    }

    public function testResolveReturnsFileNameWhenGiven()
    {
        $config = [
            'name' => 'jadu/test',
        ];

        $this->assertSame('package', $this->resolver->resolve('package', vfsStream::url('root'), $config));
    }

    /**
     * @dataProvider packageNameProvider
     */
    public function testResolveGeneratesFileNameFromPackageName($packageName, $expectedFileName)
    {
        $config = [
            'name' => $packageName,
        ];

        $this->assertSame($expectedFileName, $this->resolver->resolve(null, vfsStream::url('root'), $config));
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

        $this->assertSame($expectedFileName, $this->resolver->resolve(null, vfsStream::url('root'), $config));
    }

    public function versionProvider()
    {
        return [
            ['1.0.0', 'jadu_test_1.0.0'],
            ['1.2.0-9298a2a08a460f2e3c16a71bb01d472af07137ba', 'jadu_test_1.2.0-9298a2a08a460f2e3c16a71bb01d472af07137ba'],
            ['$$$$', 'jadu_test'],
        ];
    }

    /**
     * @expectedException RuntimeException
     */
    public function testResolveThrowsExceptionWhenVersionFileCannotBeFound()
    {
        $config = [
            'name' => 'jadu/test',
            'package' => [
                'version' => 'VERSION',
            ],
        ];

        $this->resolver->resolve(null, vfsStream::url('root'), $config);
    }

    /**
     * @dataProvider invalidFileNameProvider
     */
    public function testResolveGeneratesFileNameFromPackageNameWhenFileNameInvalid($fileName)
    {
        $config = [
            'name' => 'jadu/test',
        ];

        $this->assertSame('jadu_test', $this->resolver->resolve($fileName, vfsStream::url('root'), $config));
    }

    public function invalidFileNameProvider()
    {
        return [
            [null],
            [''],
            ['     '],
            ['___'],
            ['package$$$'],
        ];
    }
}
