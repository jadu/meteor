<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class CheckModuleCmsDependencyHandlerTest extends TestCase
{
    private $handler;

    protected function setUp(): void
    {
        $this->handler = new CheckModuleCmsDependencyHandler(new NullIO());
    }

    /**
     * @dataProvider versionProvider
     */
    public function testChecksWorkingDirVersionAndInstallDoesntHaveVersion($moduleCmsDependency, $version, $expectedResult)
    {
        vfsStream::setup('root', null, [
            'working' => [
                'MODULE_CMS_DEPENDENCY' => $moduleCmsDependency,
                'VERSION' => $version,
            ],
            'install' => [
            ],
        ]);

        $task = new CheckModuleCmsDependency(vfsStream::url('root/working'), vfsStream::url('root/install'));
        $this->assertSame($expectedResult, $this->handler->handle($task));
    }

    /**
     * @dataProvider versionProvider
     */
    public function testChecksWorkingDirVersionA($moduleCmsDependency, $version, $expectedResult)
    {
        vfsStream::setup('root', null, [
            'working' => [
                'MODULE_CMS_DEPENDENCY' => $moduleCmsDependency,
                'VERSION' => $version,
            ],
            'install' => [
                'VERSION' => '1.0.0',
            ],
        ]);

        $task = new CheckModuleCmsDependency(vfsStream::url('root/working'), vfsStream::url('root/install'));
        $this->assertSame($expectedResult, $this->handler->handle($task));
    }

    public function testReturnsTrueIfModuleCmsDependencyFileNotFoundInWorkingDir()
    {
        vfsStream::setup('root', null, [
            'working' => [],
            'install' => [],
        ]);

        $task = new CheckModuleCmsDependency(vfsStream::url('root/working'), vfsStream::url('root/install'));
        $this->assertTrue($this->handler->handle($task));
    }

    public function testReturnsTrueIfCmsVersionFileNotFoundInInstallDir()
    {
        vfsStream::setup('root', null, [
            'working' => [
                'MODULE_CMS_DEPENDENCY' => '13.7.0',
            ],
            'install' => [],
        ]);

        $task = new CheckModuleCmsDependency(vfsStream::url('root/working'), vfsStream::url('root/install'));
        $this->assertTrue($this->handler->handle($task));
    }

    public function testThrowsExceptionWhenVersionConstraintIsInvalid()
    {
        static::expectException(UnexpectedValueException::class);

        vfsStream::setup('root', null, [
            'working' => [
                'MODULE_CMS_DEPENDENCY' => '!! this is not a valid constraint',
            ],
            'install' => [
                'VERSION' => '1.2.3',
            ],
        ]);

        $task = new CheckModuleCmsDependency(vfsStream::url('root/working'), vfsStream::url('root/install'));
        $this->handler->handle($task);
    }

    /**
     * @dataProvider versionProvider
     */
    public function testChecksModuleCmsDependency($moduleCmsDependency, $version, $expectedResult)
    {
        vfsStream::setup('root', null, [
            'working' => [
                'MODULE_CMS_DEPENDENCY' => $moduleCmsDependency,
            ],
            'install' => [
                'VERSION' => $version,
            ],
        ]);

        $task = new CheckModuleCmsDependency(vfsStream::url('root/working'), vfsStream::url('root/install'));
        $this->assertSame($expectedResult, $this->handler->handle($task));
    }

    public function versionProvider()
    {
        return [
            // Same version
            ['13.7.0', '13.7.0', true],

            // Installed version is newer
            ['13.7.0', '13.8.0', true],
            ['13.7.0', '13.7.3', true],
            ['13.7.0', '14.0.0', true],

            // Installed version is older
            ['13.7.0', '13.6.0', false],
            ['13.7.3', '13.7.0', false],
            ['14.0.0', '13.6.0', false],

            // Version constraint check
            ['^13', '13.6.0', true],
            ['^13', '12.2.0', false],
            ['~13.6.0', '13.6.0', true],
            ['~13.6.0', '13.7.0', false],
            ['=13.6.0', '13.6.0', true],
            ['=13.6.0', '13.7.0', false],
            ['13.6.0 - 13.6.4', '13.6.3', true],
            ['13.6.0 - 13.6.4', '13.6.5', false],
        ];
    }
}
