<?php

namespace Meteor\Filesystem;

use Meteor\Filesystem\Finder\FinderFactory;
use Meteor\IO\NullIO;
use Mockery;
use org\bovigo\vfs\vfsStream;
use PHPUnit_Framework_TestCase;
use RuntimeException;
use Symfony\Component\Finder\Finder;

class FilesystemTest extends PHPUnit_Framework_TestCase
{
    private $finderFactory;
    private $output;
    private $io;
    private $filesystem;

    public function setUp()
    {
        $this->finderFactory = Mockery::mock(FinderFactory::class);

        $this->finderFactory->shouldReceive('create')
            ->andReturnUsing(function ($sourceDir) {
                $finder = new Finder();
                $finder->in($sourceDir);

                return $finder;
            })
            ->byDefault();

        $this->io = new NullIO();

        $this->filesystem = new Filesystem($this->finderFactory, $this->io);

        vfsStream::setup('root');
    }

    public function testCreateTempDirectory()
    {
        $path = $this->filesystem->createTempDirectory();

        $this->assertTrue(is_dir($path));
    }

    public function testCreateTempDirectoryInDirectory()
    {
        $path = $this->filesystem->createTempDirectory(vfsStream::url('root'));

        $this->assertContains(vfsStream::url('root'), $path);
        $this->assertTrue(is_dir($path));
    }

    public function testCreateTempDirectoryReturnsRandomDirectoryNames()
    {
        $paths = [];
        for ($i = 0; $i < 100; ++$i) {
            $paths[] = $this->filesystem->createTempDirectory();
        }

        $paths = array_unique($paths);

        $this->assertCount(100, $paths);
    }

    public function testEnsureDirectoryExistsCreatesDirectoryIfItDoesNotExist()
    {
        $this->filesystem->ensureDirectoryExists(vfsStream::url('root/test'));

        $this->assertTrue(is_dir(vfsStream::url('root/test')));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testEnsureDirectoryExistsThrowsExceptionIfFileExists()
    {
        file_put_contents(vfsStream::url('root/test'), 'Some text');

        $this->filesystem->ensureDirectoryExists(vfsStream::url('root/test'));
    }

    public function testFindFilesWithRelativePaths()
    {
        vfsStream::setup('root', null, [
            'public_html' => [
                'index.html' => '',
            ],
            'var' => [
                'config' => [
                    'system.xml' => '',
                ],
            ],
        ]);

        $sourceDir = vfsStream::url('root');

        $finder = new Finder();
        $finder->in($sourceDir);

        $this->finderFactory->shouldReceive('create')
            ->with($sourceDir, null, null)
            ->andReturn($finder);

        $files = $this->filesystem->findFiles($sourceDir);

        $this->assertEquals([
            'public_html',
            'public_html/index.html',
            'var',
            'var/config',
            'var/config/system.xml',
        ], $files);
    }

    public function testFindFilesWithRelativePathsWhenSourceDirInputDiffersToRealPath()
    {
        vfsStream::setup('root', null, [
            'public_html' => [
                'index.html' => '',
            ],
            'var' => [
                'config' => [
                    'system.xml' => '',
                ],
            ],
        ]);

        // The fake realpath function will lowercase the path so it will differ
        $sourceDir = vfsStream::url('root');

        $finder = new Finder();
        $finder->in($sourceDir);

        $this->finderFactory->shouldReceive('create')
            ->with($sourceDir, null, null)
            ->andReturn($finder);

        $files = $this->filesystem->findFiles(strtoupper($sourceDir));

        $this->assertEquals([
            'public_html',
            'public_html/index.html',
            'var',
            'var/config',
            'var/config/system.xml',
        ], $files);
    }

    public function testFindFilesWithAbsolutePaths()
    {
        vfsStream::setup('root', null, [
            'public_html' => [
                'index.html' => '',
            ],
            'var' => [
                'config' => [
                    'system.xml' => '',
                ],
            ],
        ]);

        $sourceDir = vfsStream::url('root');

        $finder = new Finder();
        $finder->in($sourceDir);

        $this->finderFactory->shouldReceive('create')
            ->with($sourceDir, null, null)
            ->andReturn($finder);

        $files = $this->filesystem->findFiles($sourceDir, null, false);

        $this->assertEquals([
            'vfs://root/public_html',
            'vfs://root/public_html/index.html',
            'vfs://root/var',
            'vfs://root/var/config',
            'vfs://root/var/config/system.xml',
        ], $files);
    }

    public function testFindFilesWithFilters()
    {
        $sourceDir = vfsStream::url('root');

        $finder = new Finder();
        $finder->in($sourceDir);

        $this->finderFactory->shouldReceive('create')
            ->with($sourceDir, ['/**'], null)
            ->andReturn($finder)
            ->once();

        $this->filesystem->findFiles($sourceDir, ['/**']);
    }

    public function testFindFilesWithFiltersRealFinderFactory()
    {
        vfsStream::setup('root', null, [
            'public_html' => [
                'index.html' => '',
            ],
            'var' => [
                'config' => [
                    'system.xml' => '',
                ],
                'file.cache' => ''
            ],
        ]);

        $sourceDir = vfsStream::url('root');

        $filters = ['!/var'];

        $finder = new Finder();
        $finder->in($sourceDir);

        $filesystem = new Filesystem(new FinderFactory(), $this->io);

        $files = $filesystem->findFiles($sourceDir, $filters);

        $this->assertEquals([
            'public_html',
            'public_html/index.html',
        ], $files);
    }

    public function testFindNewFiles()
    {
        vfsStream::setup('root', null, [
            'base' => [
                'public_html' => [
                    'index.html' => '',
                ],
                'var' => [
                    'config' => [
                        'system.xml' => '',
                    ],
                ],
            ],
            'target' => [
                'public_html' => [
                    'index.html' => '',
                ],
                'var' => [],
            ],
        ]);

        $baseDir = vfsStream::url('root/base');
        $targetDir = vfsStream::url('root/target');

        $finder = new Finder();
        $finder->in($baseDir);

        $this->finderFactory->shouldReceive('create')
            ->with($baseDir, null, null)
            ->andReturn($finder);

        $files = $this->filesystem->findNewFiles($baseDir, $targetDir);

        $this->assertEquals([
            'var/config',
            'var/config/system.xml',
        ], $files);
    }

    public function testCopyDirectory()
    {
        vfsStream::setup('root', null, [
            'source' => [
                'index.html' => '',
                'var' => [
                    'config' => [
                        'system.xml' => '',
                    ],
                    'cache' => [],
                ],
            ],
            'target' => [],
        ]);

        $this->assertTrue($this->filesystem->copyDirectory(vfsStream::url('root/source'), vfsStream::url('root/target')));

        $this->assertTrue(is_file(vfsStream::url('root/target/index.html')));
        $this->assertTrue(is_file(vfsStream::url('root/target/var/config/system.xml')));
        $this->assertTrue(is_dir(vfsStream::url('root/target/var/cache')));
    }

    public function testCopyDirectoryReturnsFalseWhenEmpty()
    {
        vfsStream::setup('root', null, [
            'source' => [],
            'target' => [],
        ]);

        $this->assertFalse($this->filesystem->copyDirectory(vfsStream::url('root/source'), vfsStream::url('root/target')));
    }

    public function testCopyDirectoryWithFiltersOnlyCopiesFilteredFiles()
    {
        vfsStream::setup('root', null, [
            'source' => [
                'index.html' => '',
            ],
            'target' => [],
        ]);

        $finder = new Finder();
        $finder->in(vfsStream::url('root/source'));

        $this->finderFactory->shouldReceive('create')
            ->with(vfsStream::url('root/source'), ['/**'], null)
            ->andReturn($finder)
            ->once();

        $this->filesystem->copyDirectory(vfsStream::url('root/source'), vfsStream::url('root/target'), ['/**']);

        $this->assertTrue(is_file(vfsStream::url('root/target/index.html')));
    }

    public function testRemoveDirectory()
    {
        vfsStream::setup('root', null, [
            'source' => [
                'index.html' => '',
                'vendor' => [
                    'org' => [
                        'package' => [
                            'file.html' => ''
                        ]
                    ]
                ]
            ],
            'target' => [],
        ]);

        $this->assertTrue(is_file(vfsStream::url('root/source/vendor/org/package/file.html')));

        $this->filesystem->removeDirectory(vfsStream::url('root/source/vendor'));
        $this->assertFalse(is_file(vfsStream::url('root/source/vendor/org/package/file.html')));
    }


    public function testSwapDirectory()
    {
        vfsStream::setup('root', null, [
            'source' => [
                'index.html' => '',
                'vendor' => [
                    'org' => [
                        'package' => [
                            'file_a.html' => '',
                            'file_b.html' => ''
                        ]
                    ]
                ]
            ],
            'target' => [
                'thing.html' => '',
                'vendor' => [
                    'org' => [
                        'package' => [
                            'file_b.html' => '',
                            'file_c.html' => ''
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertTrue(is_file(vfsStream::url('root/target/vendor/org/package/file_c.html')));

        $this->filesystem->swapDirectory(vfsStream::url('root/source'), vfsStream::url('root/target'), '/vendor');

        $this->assertTrue(is_file(vfsStream::url('root/target/vendor/org/package/file_a.html')));
        $this->assertFalse(is_file(vfsStream::url('root/target/vendor/org/package/file_c.html')));
    }
}

function sys_get_temp_dir()
{
    return vfsStream::url('root');
}

function realpath($path)
{
    return strtolower($path);
}
