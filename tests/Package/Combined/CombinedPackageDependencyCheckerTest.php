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
        $this->assertTrue($this->checker->check(vfsStream::url('root'), []));
    }

    public function testCheckWhenRequirementsMet()
    {
        $config = [
            'package' => [
                'combine' => [
                    'jadu/cms' => '13.7.0',
                ],
            ],
            'combined' => [
                [
                    'name' => 'jadu/cms',
                ],
            ],
        ];

        $this->assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }

    /**
     * @expectedException Meteor\Package\Combined\Exception\CombinedPackageDependenciesException
     */
    public function testCheckThrowsExceptionWhenRequiredPackageMissing()
    {
        $config = [
            'package' => [
                'combine' => [
                    'jadu/cms' => '13.7.0',
                ],
            ],
        ];

        $this->assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }

    /**
     * @expectedException Meteor\Package\Combined\Exception\CombinedPackageDependenciesException
     */
    public function testChecksRequirementsRecursively()
    {
        $config = [
            'package' => [
                'combine' => [
                    'jadu/xfp' => '3.7.0',
                ],
            ],
            'combined' => [
                [
                    'name' => 'jadu/xfp',
                    'package' => [
                        'combine' => [
                            'jadu/cms' => '13.7.0',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }

    public function testCheckWhenVersionRequirementsMet()
    {
        $config = [
            'package' => [
                'combine' => [
                    'jadu/cms' => '13.7.0',
                ],
            ],
            'combined' => [
                [
                    'name' => 'jadu/cms',
                    'package' => [
                        'version' => 'VERSION',
                    ],
                ],
            ],
        ];

        vfsStream::setup('root', null, [
            'to_patch' => [
                'VERSION' => '13.7.0',
            ],
        ]);

        $this->assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }

    /**
     * @expectedException Meteor\Package\Combined\Exception\CombinedPackageDependenciesException
     */
    public function testCheckThrowsExceptionWhenVersionRequirementIsNotMet()
    {
        $config = [
            'package' => [
                'combine' => [
                    'jadu/cms' => '13.7.0',
                ],
            ],
            'combined' => [
                [
                    'name' => 'jadu/cms',
                    'package' => [
                        'version' => 'VERSION',
                    ],
                ],
            ],
        ];

        vfsStream::setup('root', null, [
            'to_patch' => [
                'VERSION' => '13.5.0',
            ],
        ]);

        $this->assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }

    /**
     * @expectedException Meteor\Package\Combined\Exception\CombinedPackageDependenciesException
     */
    public function testChecksVersionRequirementsRecursively()
    {
        $config = [
            'package' => [
                'combine' => [
                    'jadu/xfp' => '3.7.0',
                ],
            ],
            'combined' => [
                [
                    'name' => 'jadu/xfp',
                    'package' => [
                        'version' => 'XFP_VERSION',
                        'combine' => [
                            'jadu/cms' => '13.7.0',
                        ],
                    ],
                ],
                [
                    'name' => 'jadu/cms',
                    'package' => [
                        'version' => 'VERSION',
                    ],
                ],
            ],
        ];

        vfsStream::setup('root', null, [
            'to_patch' => [
                'XFP_VERSION' => '3.7.0',
                'VERSION' => '12.0.0',
            ],
        ]);

        $this->assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }
}
