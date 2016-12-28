<?php

namespace Meteor\Package\Composer;

use org\bovigo\vfs\vfsStream;

class ComposerDependencyCheckerTest extends \PHPUnit_Framework_TestCase
{
    private $checker;

    public function setUp()
    {
        $this->checker = new ComposerDependencyChecker();

        vfsStream::setup('root');
    }

    public function testGetRequirementsReturnsRequirements()
    {
        vfsStream::setup('root', null, [
            'composer.json' => file_get_contents(__DIR__ . '/Fixtures/composer.json'),
        ]);

        $requirements = $this->checker->getRequirements(vfsStream::url('root'));

        $this->assertCount(3, $requirements);

        $this->assertInstanceOf('Meteor\Package\Composer\ComposerPhpVersion', $requirements[0]);
        $this->assertEquals('>=5.3.2', $requirements[0]->getVersionConstraint());

        $this->assertInstanceOf('Meteor\Package\Composer\ComposerRequirement', $requirements[1]);
        $this->assertEquals('jadu/cms-dependencies', $requirements[1]->getPackageName());
        $this->assertEquals('~13.6.0', $requirements[1]->getVersionConstraint());

        $this->assertInstanceOf('Meteor\Package\Composer\ComposerRequirement', $requirements[2]);
        $this->assertEquals('symfony/symfony', $requirements[2]->getPackageName());
        $this->assertEquals('~2.6.11', $requirements[2]->getVersionConstraint());
    }

    public function testGetRequirementsIgnoresPhpExtensionRequirements()
    {
        vfsStream::setup('root', null, [
            'composer.json' => file_get_contents(__DIR__ . '/Fixtures/composer.json'),
        ]);

        $requirements = $this->checker->getRequirements(vfsStream::url('root'));

        $this->assertCount(3, $requirements);
        $this->assertEquals('>=5.3.2', $requirements[0]->getVersionConstraint());
        $this->assertEquals('jadu/cms-dependencies', $requirements[1]->getPackageName());
        $this->assertEquals('~13.6.0', $requirements[1]->getVersionConstraint());
        $this->assertEquals('symfony/symfony', $requirements[2]->getPackageName());
        $this->assertEquals('~2.6.11', $requirements[2]->getVersionConstraint());
    }

    public function testGetRequirementsReturnsEmptyArrayWhenComposerJsonNotFound()
    {
        vfsStream::setup('root');

        $this->assertSame([], $this->checker->getRequirements(vfsStream::url('root')));
    }

    /**
     * @expectedException \Meteor\Package\Composer\Exception\ComposerDependenciesException
     */
    public function testGetRequirementsThrowsExceptionWhenComposerJsonCannotBeParsed()
    {
        vfsStream::setup('root', null, [
            'composer.json' => '!!!',
        ]);

        $this->checker->getRequirements(vfsStream::url('root'));
    }

    public function testAddRequirements()
    {
        $requirements = [
            new ComposerRequirement('jadu/cms-dependencies', '~13.6.0'),
            new ComposerRequirement('symfony/symfony', '~2.6.11'),
        ];

        $this->assertSame([
            'package' => [
                'composer' => [
                    'jadu/cms-dependencies' => '~13.6.0',
                    'symfony/symfony' => '~2.6.11',
                ],
            ],
        ], $this->checker->addRequirements($requirements, []));
    }

    public function testAddRequirementsReplacesExistingRequirements()
    {
        $requirements = [
            new ComposerRequirement('jadu/cms-dependencies', '~13.6.0'),
            new ComposerRequirement('symfony/symfony', '~2.6.11'),
        ];

        $config = [
            'package' => [
                'composer' => [
                    'guzzle/guzzle' => '^3.0',
                ],
            ],
        ];

        $this->assertSame([
            'package' => [
                'composer' => [
                    'jadu/cms-dependencies' => '~13.6.0',
                    'symfony/symfony' => '~2.6.11',
                ],
            ],
        ], $this->checker->addRequirements($requirements, $config));
    }

    public function testCheckWhenHasNoRequirementsAndLockFileMissing()
    {
        $this->checker->check(vfsStream::url('root'), []);
    }

    public function testCheckWhenRequiredPackagesInLockFile()
    {
        vfsStream::setup('root', null, [
            'composer.lock' => file_get_contents(__DIR__ . '/Fixtures/composer.lock'),
        ]);

        $this->checker->check(vfsStream::url('root'), [
            'combined' => [
                [
                    'name' => 'test1',
                    'package' => [
                        'composer' => [
                            'jadu/cms-dependencies' => '~13.6.0',
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function testCheckIsCaseInsensitive()
    {
        vfsStream::setup('root', null, [
            'composer.lock' => file_get_contents(__DIR__ . '/Fixtures/composer.lock'),
        ]);

        $this->checker->check(vfsStream::url('root'), [
            'combined' => [
                [
                    'name' => 'test1',
                    'package' => [
                        'composer' => [
                            'jadu/CMS-dependencies' => '~13.6.0',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @expectedException \Meteor\Package\Composer\Exception\ComposerDependenciesException
     */
    public function testCheckThrowsExceptionWhenComposerLockCannotBeParsed()
    {
        vfsStream::setup('root', null, [
            'composer.lock' => '!!!',
        ]);

        $this->checker->check(vfsStream::url('root'), [
            'combined' => [
                [
                    'name' => 'test1',
                    'package' => [
                        'composer' => [
                            'jadu/cms-dependencies' => '~13.6.0',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @expectedException \Meteor\Package\Composer\Exception\ComposerDependenciesException
     */
    public function testCheckThrowsExceptionWhenHasRequiredPackagesAndLockFileMissing()
    {
        $this->checker->check(vfsStream::url('root'), [
            'combined' => [
                [
                    'name' => 'test1',
                    'package' => [
                        'composer' => [
                            'jadu/cms-dependencies' => '~13.6.0',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @expectedException \Meteor\Package\Composer\Exception\ComposerDependenciesException
     */
    public function testCheckThrowsExceptionWhenRequiredPackageMissingFromLockFile()
    {
        vfsStream::setup('root', null, [
            'composer.lock' => file_get_contents(__DIR__ . '/Fixtures/composer.lock'),
        ]);

        $this->checker->check(vfsStream::url('root'), [
            'combined' => [
                [
                    'name' => 'test1',
                    'package' => [
                        'composer' => [
                            'jadu/this-does-not-exist' => '~9.9.9',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @expectedException \Meteor\Package\Composer\Exception\ComposerDependenciesException
     */
    public function testCheckThrowsExceptionWhenVersionInLockFileDoesNotSatisfyRequiredPackageConstraint()
    {
        vfsStream::setup('root', null, [
            'composer.lock' => file_get_contents(__DIR__ . '/Fixtures/composer.lock'),
        ]);

        $this->checker->check(vfsStream::url('root'), [
            'combined' => [
                [
                    'name' => 'test1',
                    'package' => [
                        'composer' => [
                            'jadu/cms-dependencies' => '~14.6.0',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
