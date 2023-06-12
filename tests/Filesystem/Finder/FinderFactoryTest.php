<?php

namespace Meteor\Filesystem\Finder;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class FinderFactoryTest extends TestCase
{
    private $finderFactory;

    protected function setUp(): void
    {
        $this->finderFactory = new FinderFactory();
    }

    /**
     * @dataProvider filterProvider
     */
    public function testFiltersPaths(array $structure, array $filters, array $expectedFiles)
    {
        vfsStream::setup('root', null, $structure);

        $finder = $this->finderFactory->create(vfsStream::url('root'), $filters);

        $foundFiles = [];
        foreach ($finder as $file) {
            $foundFiles[] = preg_replace('/^' . preg_quote(vfsStream::url('root') . '/', '/') . '/', '', $file->getPathname());
        }

        static::assertEquals($expectedFiles, $foundFiles);
    }

    public function filterProvider()
    {
        return [
            [
                [
                    'index.html' => '',
                    'XFP_VERSION' => '',
                ],
                [],
                [
                    'index.html',
                    'XFP_VERSION',
                ],
            ],
            [
                [
                    'index.html' => '',
                    'XFP_VERSION' => '',
                ],
                [
                    '**',
                ],
                [
                    'index.html',
                    'XFP_VERSION',
                ],
            ],
            [
                [
                    'index.html' => '',
                ],
                [
                    'index.html',
                ],
                [
                    'index.html',
                ],
            ],
            [
                [
                    'index.html' => '',
                ],
                [
                    '!index.html',
                ],
                [
                ],
            ],
            [
                [
                    'XFP_VERSION' => '',
                ],
                [
                    '*VERSION',
                ],
                [
                    'XFP_VERSION',
                ],
            ],
            [
                [
                    'var' => [
                        'XFP_VERSION' => '',
                    ],
                    'VERSION' => '',
                ],
                [
                    '*VERSION',
                ],
                [
                    'var/XFP_VERSION',
                    'VERSION',
                ],
            ],
            [
                [
                    'var' => [
                        'XFP_VERSION' => '1.3.2',
                    ],
                    'VERSION' => '',
                ],
                [
                    '/*VERSION',
                ],
                [
                    'VERSION',
                ],
            ],
            [
                [
                    'var' => [
                        'XFP_VERSION' => '1.3.2',
                    ],
                    'VERSION' => '',
                ],
                [
                    '**VERSION',
                    '!/*VERSION',
                ],
                [
                    'var/XFP_VERSION',
                ],
            ],
            [
                [
                    'backups' => [
                        '20160701102030' => [
                            'CORE_MIGRATION_NUMBER' => '2',
                        ],
                    ],
                    'CORE_MIGRATION_NUMBER' => '1',
                ],
                [
                    '/*_MIGRATION_NUMBER',
                ],
                [
                    'CORE_MIGRATION_NUMBER',
                ],
            ],
            [
                [
                    'backups' => [
                        '20160701102030' => [
                            'CORE_MIGRATION_NUMBER' => '2',
                        ],
                    ],
                    'CORE_MIGRATION_NUMBER' => '1',
                ],
                [
                    '*_MIGRATION_NUMBER',
                ],
                [
                    'backups/20160701102030/CORE_MIGRATION_NUMBER',
                    'CORE_MIGRATION_NUMBER',
                ],
            ],
            [
                [
                    'public_html' => [
                        '.htaccess' => '',
                    ],
                ],
                [
                    '/public_html/**',
                ],
                [
                    'public_html/.htaccess',
                ],
            ],
            [
                [
                    'public_html' => [
                        '.htaccess' => '',
                    ],
                ],
                [
                    '.htaccess',
                ],
                [
                    'public_html/.htaccess',
                ],
            ],
            [
                [
                    'public_html' => [
                        '.htaccess' => '',
                    ],
                    'vendor' => [
                        'org' => [
                            'package' => 'file.html',
                        ],
                    ],
                ],
                [
                    '**',
                    '!/vendor',
                ],
                [
                    'public_html',
                    'public_html/.htaccess',
                ],
            ],
            [
                [
                    'public_html' => [
                        '.htaccess' => '',
                        'index.html' => '',
                    ],
                    'node_modules' => [
                        'item.txt' => '',
                        'module_1' => [
                            'abc.txt' => '',
                        ],
                    ],
                    'vendor' => [
                        'org' => [
                            'package' => 'file.html',
                        ],
                    ],
                ],
                [
                    '/public_html',
                    '!/public_html/.htaccess',
                ],
                [
                    'public_html',
                    'public_html/index.html',
                ],
            ],
        ];
    }

    /**
     * @dataProvider generatePatternProvider
     *
     * @param string $filter
     * @param string $directorySeparator
     * @param array $expected
     */
    public function testGeneratePattern($filter, $directorySeparator, $expected)
    {
        $pattern = $this->finderFactory->generatePattern($filter, $directorySeparator);

        static::assertEquals($expected, $pattern);
    }

    public function generatePatternProvider()
    {
        return [
            [
                'filter' => '!/vendor',
                'directorySeparator' => '/',
                'expected' => ['/^vendor(?=$|\/)/', true],
            ],
            [
                'filter' => '!/vendor',
                'directorySeparator' => '\\',
                'expected' => ['/^vendor(?=$|\\\)/', true],
            ],
            [
                'filter' => '/vendor',
                'directorySeparator' => '\\',
                'expected' => ['/^vendor(?=$|\\\)/', false],
            ],
            [
                'filter' => '/public_html/site/styles',
                'directorySeparator' => '\\',
                'expected' => ['/^public_html\\\site\\\styles(?=$|\\\)/', false],
            ],
            [
                'filter' => '/public_html/site/styles',
                'directorySeparator' => '/',
                'expected' => ['/^public_html\/site\/styles(?=$|\/)/', false],
            ],
        ];
    }
}
