<?php

namespace Meteor\Patch\Task;

use Meteor\Patch\Version\VersionComparer;
use Meteor\Patch\Version\VersionDiff;
use Mockery;

class DisplayVersionInfoHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $io;
    private $versionComparer;
    private $handler;

    public function setUp()
    {
        $this->io = Mockery::mock('Meteor\IO\IOInterface', array(
            'text' => null,
            'newLine' => null,
        ));
        $this->versionComparer = Mockery::mock('Meteor\Patch\Version\VersionComparer');
        $this->handler = new DisplayVersionInfoHandler($this->io, $this->versionComparer);
    }

    public function testHandleDoesntOutputWhenThereAreNoVersions()
    {
        $config = array(
            'name' => 'jadu/cms',
            'combined' => array(
            ),
        );

        $versions = array(
        );

        $this->versionComparer->shouldReceive('comparePackage')
            ->with('working', 'install', $config)
            ->andReturn($versions)
            ->once();

        $this->io->shouldNotReceive('table')
            ->with(
                Mockery::any(),
                array(
                )
            );

        $this->handler->handle(new DisplayVersionInfo('working', 'install'), $config);
    }

    public function testHandleOutputsPackageVersionInfo()
    {
        $config = array(
            'name' => 'jadu/cms',
            'package' => array(
                'version' => 'VERSION',
            ),
            'combined' => array(
                array(
                    'name' => 'jadu/xfp',
                    'package' => array(
                        'version' => 'XFP_VERSION',
                    ),
                ),
                array(
                    'name' => 'jadu/cp',
                    'package' => array(
                        'version' => 'CP_VERSION',
                    ),
                ),
            ),
        );

        $versions = array(
            new VersionDiff('jadu/cms', 'VERSION', '1.1.0', '1.0.0'),
            new VersionDiff('jadu/xfp', 'XFP_VERSION', '1.1.0', '1.2.0'),
            new VersionDiff('jadu/cp', 'CP_VERSION', '1.0.0', '1.0.0'),
        );

        $this->versionComparer->shouldReceive('comparePackage')
            ->with('working', 'install', $config)
            ->andReturn($versions)
            ->once();

        $this->io->shouldReceive('table')
            ->with(
                Mockery::any(),
                array(
                    array('jadu/cms', 'VERSION', '1.0.0', '1.1.0', '<fg=green>Newer</>'),
                    array('jadu/xfp', 'XFP_VERSION', '1.2.0', '1.1.0', '<fg=red>Older</>'),
                    array('jadu/cp', 'CP_VERSION', '1.0.0', '1.0.0', '<fg=yellow>No change</>'),
                )
            )
            ->once();

        $this->handler->handle(new DisplayVersionInfo('working', 'install'), $config);
    }
}
