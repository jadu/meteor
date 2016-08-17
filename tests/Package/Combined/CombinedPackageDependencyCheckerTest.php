<?php

namespace Meteor\Package\Combined;

use org\bovigo\vfs\vfsStream;

class CombinedPackageDependencyCheckerTest extends \PHPUnit_Framework_TestCase
{
    private $checker;

    public function setUp()
    {
        $this->checker = new CombinedPackageDependencyChecker();

        vfsStream::setup('root');
    }

    public function testCheckWhenHasNoRequirements()
    {
        $this->assertTrue($this->checker->check(vfsStream::url('root'), array()));
    }

    public function testCheckWhenRequirementsMet()
    {
        $config = array(
            'package' => array(
                'combine' => array(
                    'jadu/cms' => '13.7.0',
                ),
            ),
            'combined' => array(
                array(
                    'name' => 'jadu/cms',
                ),
            ),
        );

        $this->assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }

    /**
     * @expectedException Meteor\Package\Combined\Exception\CombinedPackageDependenciesException
     */
    public function testCheckThrowsExceptionWhenRequiredPackageMissing()
    {
        $config = array(
            'package' => array(
                'combine' => array(
                    'jadu/cms' => '13.7.0',
                ),
            ),
        );

        $this->assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }

    /**
     * @expectedException Meteor\Package\Combined\Exception\CombinedPackageDependenciesException
     */
    public function testChecksRequirementsRecursively()
    {
        $config = array(
            'package' => array(
                'combine' => array(
                    'jadu/xfp' => '3.7.0',
                ),
            ),
            'combined' => array(
                array(
                    'name' => 'jadu/xfp',
                    'package' => array(
                        'combine' => array(
                            'jadu/cms' => '13.7.0',
                        ),
                    ),
                ),
            ),
        );

        $this->assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }

    public function testCheckWhenVersionRequirementsMet()
    {
        $config = array(
            'package' => array(
                'combine' => array(
                    'jadu/cms' => '13.7.0',
                ),
            ),
            'combined' => array(
                array(
                    'name' => 'jadu/cms',
                    'package' => array(
                        'version' => 'VERSION',
                    ),
                ),
            ),
        );

        vfsStream::setup('root', null, array(
            'to_patch' => array(
                'VERSION' => '13.7.0',
            ),
        ));

        $this->assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }

    /**
     * @expectedException Meteor\Package\Combined\Exception\CombinedPackageDependenciesException
     */
    public function testCheckThrowsExceptionWhenVersionRequirementIsNotMet()
    {
        $config = array(
            'package' => array(
                'combine' => array(
                    'jadu/cms' => '13.7.0',
                ),
            ),
            'combined' => array(
                array(
                    'name' => 'jadu/cms',
                    'package' => array(
                        'version' => 'VERSION',
                    ),
                ),
            ),
        );

        vfsStream::setup('root', null, array(
            'to_patch' => array(
                'VERSION' => '13.5.0',
            ),
        ));

        $this->assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }

    /**
     * @expectedException Meteor\Package\Combined\Exception\CombinedPackageDependenciesException
     */
    public function testChecksVersionRequirementsRecursively()
    {
        $config = array(
            'package' => array(
                'combine' => array(
                    'jadu/xfp' => '3.7.0',
                ),
            ),
            'combined' => array(
                array(
                    'name' => 'jadu/xfp',
                    'package' => array(
                        'version' => 'XFP_VERSION',
                        'combine' => array(
                            'jadu/cms' => '13.7.0',
                        ),
                    ),
                ),
                array(
                    'name' => 'jadu/cms',
                    'package' => array(
                        'version' => 'VERSION',
                    ),
                ),
            ),
        );

        vfsStream::setup('root', null, array(
            'to_patch' => array(
                'XFP_VERSION' => '3.7.0',
                'VERSION' => '12.0.0',
            ),
        ));

        $this->assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }
}
