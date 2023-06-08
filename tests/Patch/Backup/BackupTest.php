<?php

namespace Meteor\Patch\Backup;

use Meteor\Patch\Version\VersionDiff;
use Mockery;
use PHPUnit\Framework\TestCase;

class BackupTest extends TestCase
{
    public function testGetDate()
    {
        $backup = new Backup('/path/to/20160203145217', []);

        static::assertSame('2016-02-03T14:52:17+00:00', $backup->getDate()->format('c'));
    }

    public function isValidDataProvider()
    {
        return [
            [
                ['getNewVersion' => 'dev-packagebranch-1', 'getCurrentVersion' => '1.0.0'],
            ],
            [
                ['getNewVersion' => 'dev-branc-22-33-1', 'getCurrentVersion' => 'dev-packagebranch-1'],
            ],
            [
                ['getNewVersion' => '3.4.1', 'getCurrentVersion' => 'dev-poc-release/23'],
            ],
        ];
    }

    /**
     * @dataProvider isValidDataProvider()
     */
    public function testIsValid($versions)
    {
        $versions = [
            Mockery::mock(VersionDiff::class, $versions),
        ];
        $backup = new Backup('/path/to/20160203145217', $versions);

        static::assertTrue($backup->isValid());
    }

    public function testIsValidNonDevVersions()
    {
        $versions = [
            new VersionDiff('package1', 'filename1', '3.4.1', '3.2.0'),
        ];
        $backup = new Backup('/path/to/20160203145217', $versions);

        static::assertFalse($backup->isValid());
    }

    public function testIsValidNonDevVersionsValidVersion()
    {
        $versions = [
            new VersionDiff('package1', 'filename1', '3.2.0', '3.4.1'),
        ];
        $backup = new Backup('/path/to/20160203145217', $versions);

        static::assertTrue($backup->isValid());
    }
}
