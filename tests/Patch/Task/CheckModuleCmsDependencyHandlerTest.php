<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use org\bovigo\vfs\vfsStream;

class CheckModuleCmsDependencyHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $handler;

    public function setUp()
    {
        $this->handler = new CheckModuleCmsDependencyHandler(new NullIO());
    }

    /**
     * @dataProvider versionProvider
     */
    public function testChecksWorkingDirVersionAndInstallDoesntHaveVersion($moduleCmsDependency, $version, $expectedResult)
    {
        vfsStream::setup('root', null, array(
            'working' => array(
                'MODULE_CMS_DEPENDENCY' => $moduleCmsDependency,
                'VERSION' => $version,
            ),
            'install' => array(
            ),
        ));

        $task = new CheckModuleCmsDependency(vfsStream::url('root/working'), vfsStream::url('root/install'));
        $this->assertSame($expectedResult, $this->handler->handle($task));
    }

    /**
     * @dataProvider versionProvider
     */
    public function testChecksWorkingDirVersionA($moduleCmsDependency, $version, $expectedResult)
    {
        vfsStream::setup('root', null, array(
            'working' => array(
                'MODULE_CMS_DEPENDENCY' => $moduleCmsDependency,
                'VERSION' => $version,
            ),
            'install' => array(
                'VERSION' => '1.0.0',
            ),
        ));

        $task = new CheckModuleCmsDependency(vfsStream::url('root/working'), vfsStream::url('root/install'));
        $this->assertSame($expectedResult, $this->handler->handle($task));
    }

    public function testReturnsTrueIfModuleCmsDependencyFileNotFoundInWorkingDir()
    {
        vfsStream::setup('root', null, array(
            'working' => array(),
            'install' => array(),
        ));

        $task = new CheckModuleCmsDependency(vfsStream::url('root/working'), vfsStream::url('root/install'));
        $this->assertTrue($this->handler->handle($task));
    }

    public function testReturnsTrueIfCmsVersionFileNotFoundInInstallDir()
    {
        vfsStream::setup('root', null, array(
            'working' => array(
                'MODULE_CMS_DEPENDENCY' => '13.7.0',
            ),
            'install' => array(),
        ));

        $task = new CheckModuleCmsDependency(vfsStream::url('root/working'), vfsStream::url('root/install'));
        $this->assertTrue($this->handler->handle($task));
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testThrowsExceptionWhenVersionConstraintIsInvalid()
    {
        vfsStream::setup('root', null, array(
            'working' => array(
                'MODULE_CMS_DEPENDENCY' => '!! this is not a valid constraint',
            ),
            'install' => array(
                'VERSION' => '1.2.3',
            ),
        ));

        $task = new CheckModuleCmsDependency(vfsStream::url('root/working'), vfsStream::url('root/install'));
        $this->handler->handle($task);
    }

    /**
     * @dataProvider versionProvider
     */
    public function testChecksModuleCmsDependency($moduleCmsDependency, $version, $expectedResult)
    {
        vfsStream::setup('root', null, array(
            'working' => array(
                'MODULE_CMS_DEPENDENCY' => $moduleCmsDependency,
            ),
            'install' => array(
                'VERSION' => $version,
            ),
        ));

        $task = new CheckModuleCmsDependency(vfsStream::url('root/working'), vfsStream::url('root/install'));
        $this->assertSame($expectedResult, $this->handler->handle($task));
    }

    public function versionProvider()
    {
        return array(
            // Same version
            array('13.7.0', '13.7.0', true),

            // Installed version is newer
            array('13.7.0', '13.8.0', true),
            array('13.7.0', '13.7.3', true),
            array('13.7.0', '14.0.0', true),

            // Installed version is older
            array('13.7.0', '13.6.0', false),
            array('13.7.3', '13.7.0', false),
            array('14.0.0', '13.6.0', false),

            // Version constraint check
            array('^13', '13.6.0', true),
            array('^13', '12.2.0', false),
            array('~13.6.0', '13.6.0', true),
            array('~13.6.0', '13.7.0', false),
            array('=13.6.0', '13.6.0', true),
            array('=13.6.0', '13.7.0', false),
            array('13.6.0 - 13.6.4', '13.6.3', true),
            array('13.6.0 - 13.6.4', '13.6.5', false),
        );
    }
}
