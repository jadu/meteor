<?php

namespace Meteor\Filesystem\Finder;

use org\bovigo\vfs\vfsStream;

class FinderFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $finderFactory;

    public function setUp()
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

        $this->assertEquals($expectedFiles, $foundFiles);
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
                    'var',
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
                            'package' => 'file.html'
                        ]
                    ]
                ],
                [
                    '!/vendor',
                ],
                [
                    'public_html',
                    'public_html/.htaccess',
                ],
            ],
        ];
    }
}
