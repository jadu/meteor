<?php

namespace Meteor\Package\Combined;

use Meteor\Package\Combined\Exception\CombinedPackageDependenciesException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class CombinedPackageDependencyCheckerTest extends TestCase
{
    private $checker;

    protected function setUp(): void
    {
        $this->checker = new CombinedPackageDependencyChecker();

        vfsStream::setup('root');
    }

    public function testCheckWhenHasNoRequirements()
    {
        static::assertTrue($this->checker->check(vfsStream::url('root'), []));
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

        static::assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }

    public function testCheckThrowsExceptionWhenRequiredPackageMissing()
    {
        static::expectException(CombinedPackageDependenciesException::class);

        $config = [
            'package' => [
                'combine' => [
                    'jadu/cms' => '13.7.0',
                ],
            ],
        ];

        static::assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }

    public function testChecksRequirementsRecursively()
    {
        static::expectException(CombinedPackageDependenciesException::class);

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

        static::assertTrue($this->checker->check(vfsStream::url('root'), $config));
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

        static::assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }

    public function testCheckThrowsExceptionWhenVersionRequirementIsNotMet()
    {
        static::expectException(CombinedPackageDependenciesException::class);

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

        static::assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }

    public function testChecksVersionRequirementsRecursively()
    {
        static::expectException(CombinedPackageDependenciesException::class);

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

        static::assertTrue($this->checker->check(vfsStream::url('root'), $config));
    }
}
