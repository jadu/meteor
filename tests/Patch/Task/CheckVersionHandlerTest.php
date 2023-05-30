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

        static::assertTrue($this->handler->handle(new CheckVersion('working', 'install', CheckVersion::GREATER_THAN_OR_EQUAL), $config));
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

        static::assertFalse($this->handler->handle(new CheckVersion('working', 'install', CheckVersion::GREATER_THAN_OR_EQUAL), $config));
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

        static::assertTrue($this->handler->handle(new CheckVersion('working', 'install', CheckVersion::LESS_THAN_OR_EQUAL), $config));
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

        static::assertFalse($this->handler->handle(new CheckVersion('working', 'install', CheckVersion::LESS_THAN_OR_EQUAL), $config));
    }

    public function developmentPackagesProvider()
    {
        return [
            [
                'dev-packagebranch-1',
                '1.0.0'
            ],
            [
                'dev-branc-22-33-1',
                'dev-packagebranch-1'
            ],
            [
                '3.4.1',
                'dev-poc-release/23'
            ],
        ];
    }

    /**
     * @dataProvider developmentPackagesProvider()
     */
    public function testIgnoresDevelopmentPackagesFromErrorWhenPatching($newVersion, $currentVersion)
    {
        $config = [
            'package' => [
                'version' => 'CLIENT_VERSION',
            ],
        ];

        $versions = [
            new VersionDiff('test', 'CLIENT_VERSION', $newVersion, $currentVersion),
        ];

        $this->versionComparer->shouldReceive('comparePackage')
            ->with('working', 'install', $config)
            ->andReturn($versions)
            ->once();

        static::assertTrue($this->handler->handle(new CheckVersion('working', 'install', CheckVersion::LESS_THAN_OR_EQUAL), $config));
    }

    /**
     * @dataProvider developmentPackagesProvider()
     */
    public function testIgnoresDevelopmentPackagesFromErrorWhenRollback($newVersion, $oldVersion)
    {
        $config = [
            'package' => [
                'version' => 'VERSION',
            ],
        ];

        $versions = [
            new VersionDiff('test', 'VERSION', $newVersion, $oldVersion),
        ];

        $this->versionComparer->shouldReceive('comparePackage')
            ->with('working', 'install', $config)
            ->andReturn($versions)
            ->once();

        static::assertTrue($this->handler->handle(new CheckVersion('working', 'install', CheckVersion::GREATER_THAN_OR_EQUAL), $config));
    }
}
