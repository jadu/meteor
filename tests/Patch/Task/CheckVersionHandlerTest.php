<?php

namespace Meteor\Patch\Task;

use Meteor\IO\NullIO;
use Meteor\Patch\Version\VersionDiff;
use Mockery;
use PHPUnit\Framework\TestCase;

class CheckVersionHandlerTest extends TestCase
{
    private $versionComparer;
    private $handler;

    protected function setUp(): void
    {
        $this->versionComparer = Mockery::mock('Meteor\Patch\Version\VersionComparer');
        $this->handler = new CheckVersionHandler(new NullIO(), $this->versionComparer);
    }

    public function testAllowsNewerVersions()
    {
        $config = [
            'package' => [
                'version' => 'VERSION',
            ],
        ];

        $versions = [
            new VersionDiff('test', 'VERSION', '1.1.0', '1.0.0'),
        ];

        $this->versionComparer->shouldReceive('comparePackage')
            ->with('working', 'install', $config)
            ->andReturn($versions)
            ->once();

        $this->assertTrue($this->handler->handle(new CheckVersion('working', 'install', CheckVersion::GREATER_THAN_OR_EQUAL), $config));
    }

    public function testPreventsOlderVersionFromBeingPatched()
    {
        $config = [
            'package' => [
                'version' => 'VERSION',
            ],
        ];

        $versions = [
            new VersionDiff('test', 'VERSION', '1.0.0', '1.1.0'),
        ];

        $this->versionComparer->shouldReceive('comparePackage')
            ->with('working', 'install', $config)
            ->andReturn($versions)
            ->once();

        $this->assertFalse($this->handler->handle(new CheckVersion('working', 'install', CheckVersion::GREATER_THAN_OR_EQUAL), $config));
    }

    public function testAllowsOlderVersionsForRollback()
    {
        $config = [
            'package' => [
                'version' => 'VERSION',
            ],
        ];

        $versions = [
            new VersionDiff('test', 'VERSION', '1.0.0', '1.1.0'),
        ];

        $this->versionComparer->shouldReceive('comparePackage')
            ->with('working', 'install', $config)
            ->andReturn($versions)
            ->once();

        $this->assertTrue($this->handler->handle(new CheckVersion('working', 'install', CheckVersion::LESS_THAN_OR_EQUAL), $config));
    }

    public function testPreventsNewerVersionFromBeingPatchedForRollback()
    {
        $config = [
            'package' => [
                'version' => 'VERSION',
            ],
        ];

        $versions = [
            new VersionDiff('test', 'VERSION', '1.1.0', '1.0.0'),
        ];

        $this->versionComparer->shouldReceive('comparePackage')
            ->with('working', 'install', $config)
            ->andReturn($versions)
            ->once();

        $this->assertFalse($this->handler->handle(new CheckVersion('working', 'install', CheckVersion::LESS_THAN_OR_EQUAL), $config));
    }
}
