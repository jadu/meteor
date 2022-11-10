<?php

namespace Meteor\Package\Composer;

use Meteor\Package\Composer\Exception\ComposerDependenciesException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ComposerDependencyCheckerTest extends TestCase
{
    private $checker;

    protected function setUp(): void
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

        static::assertCount(3, $requirements);

        static::assertInstanceOf('Meteor\Package\Composer\ComposerPhpVersion', $requirements[0]);
        static::assertEquals('>=5.3.2', $requirements[0]->getVersionConstraint());

        static::assertInstanceOf('Meteor\Package\Composer\ComposerRequirement', $requirements[1]);
        static::assertEquals('jadu/cms-dependencies', $requirements[1]->getPackageName());
        static::assertEquals('~13.6.0', $requirements[1]->getVersionConstraint());

        static::assertInstanceOf('Meteor\Package\Composer\ComposerRequirement', $requirements[2]);
        static::assertEquals('symfony/symfony', $requirements[2]->getPackageName());
        static::assertEquals('~2.6.11', $requirements[2]->getVersionConstraint());
    }

    public function testGetRequirementsIgnoresPhpExtensionRequirements()
    {
        vfsStream::setup('root', null, [
            'composer.json' => file_get_contents(__DIR__ . '/Fixtures/composer.json'),
        ]);

        $requirements = $this->checker->getRequirements(vfsStream::url('root'));

        static::assertCount(3, $requirements);
        static::assertEquals('>=5.3.2', $requirements[0]->getVersionConstraint());
        static::assertEquals('jadu/cms-dependencies', $requirements[1]->getPackageName());
        static::assertEquals('~13.6.0', $requirements[1]->getVersionConstraint());
        static::assertEquals('symfony/symfony', $requirements[2]->getPackageName());
        static::assertEquals('~2.6.11', $requirements[2]->getVersionConstraint());
    }

    public function testGetRequirementsReturnsEmptyArrayWhenComposerJsonNotFound()
    {
        vfsStream::setup('root');

        static::assertSame([], $this->checker->getRequirements(vfsStream::url('root')));
    }

    public function testGetRequirementsThrowsExceptionWhenComposerJsonCannotBeParsed()
    {
        static::expectException(ComposerDependenciesException::class);

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

        static::assertSame([
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

        static::assertSame([
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

    public function testCheckThrowsExceptionWhenComposerLockCannotBeParsed()
    {
        static::expectException(ComposerDependenciesException::class);

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

    public function testCheckThrowsExceptionWhenHasRequiredPackagesAndLockFileMissing()
    {
        static::expectException(ComposerDependenciesException::class);

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

    public function testCheckThrowsExceptionWhenRequiredPackageMissingFromLockFile()
    {
        static::expectException(ComposerDependenciesException::class);

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

    public function testCheckThrowsExceptionWhenVersionInLockFileDoesNotSatisfyRequiredPackageConstraint()
    {
        static::expectException(ComposerDependenciesException::class);

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
