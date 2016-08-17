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

        $foundFiles = array();
        foreach ($finder as $file) {
            $foundFiles[] = preg_replace('/^'.preg_quote(vfsStream::url('root').'/', '/').'/', '', $file->getPathname());
        }

        $this->assertEquals($expectedFiles, $foundFiles);
    }

    public function filterProvider()
    {
        return array(
            array(
                array(
                    'index.html' => '',
                    'XFP_VERSION' => '',
                ),
                array(
                    '**',
                ),
                array(
                    'index.html',
                    'XFP_VERSION',
                ),
            ),
            array(
                array(
                    'index.html' => '',
                ),
                array(
                    'index.html',
                ),
                array(
                    'index.html',
                ),
            ),
            array(
                array(
                    'index.html' => '',
                ),
                array(
                    '!index.html',
                ),
                array(
                ),
            ),
            array(
                array(
                    'XFP_VERSION' => '',
                ),
                array(
                    '*VERSION',
                ),
                array(
                    'XFP_VERSION',
                ),
            ),
            array(
                array(
                    'var' => array(
                        'XFP_VERSION' => '',
                    ),
                    'VERSION' => '',
                ),
                array(
                    '*VERSION',
                ),
                array(
                    'var/XFP_VERSION',
                    'VERSION',
                ),
            ),
            array(
                array(
                    'var' => array(
                        'XFP_VERSION' => '1.3.2',
                    ),
                    'VERSION' => '',
                ),
                array(
                    '/*VERSION',
                ),
                array(
                    'VERSION',
                ),
            ),
            array(
                array(
                    'var' => array(
                        'XFP_VERSION' => '1.3.2',
                    ),
                    'VERSION' => '',
                ),
                array(
                    '**VERSION',
                    '!/*VERSION',
                ),
                array(
                    'var/XFP_VERSION',
                ),
            ),
            array(
                array(
                    'backups' => array(
                        '20160701102030' => array(
                            'CORE_MIGRATION_NUMBER' => '2',
                        ),
                    ),
                    'CORE_MIGRATION_NUMBER' => '1',
                ),
                array(
                    '/*_MIGRATION_NUMBER',
                ),
                array(
                    'CORE_MIGRATION_NUMBER',
                ),
            ),
            array(
                array(
                    'backups' => array(
                        '20160701102030' => array(
                            'CORE_MIGRATION_NUMBER' => '2',
                        ),
                    ),
                    'CORE_MIGRATION_NUMBER' => '1',
                ),
                array(
                    '*_MIGRATION_NUMBER',
                ),
                array(
                    'backups/20160701102030/CORE_MIGRATION_NUMBER',
                    'CORE_MIGRATION_NUMBER',
                ),
            ),
            array(
                array(
                    'public_html' => array(
                        '.htaccess' => '',
                    ),
                ),
                array(
                    '/public_html/**',
                ),
                array(
                    'public_html/.htaccess',
                ),
            ),
            array(
                array(
                    'public_html' => array(
                        '.htaccess' => '',
                    ),
                ),
                array(
                    '.htaccess',
                ),
                array(
                    'public_html/.htaccess',
                ),
            ),
        );
    }
}
