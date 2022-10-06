<?php

namespace Meteor\Patch\Version;

use PHPUnit\Framework\TestCase;

class VersionDiffTest extends TestCase
{
    /**
     * @dataProvider lessThanProvider
     */
    public function testLessThan($newVersion, $currentVersion, $expectedResult)
    {
        $version = new VersionDiff('test', 'VERSION', $newVersion, $currentVersion);

        $this->assertSame($expectedResult, $version->isLessThan());
    }

    public function lessThanProvider()
    {
        return [
            ['1.0.0', '1.0.0', false],
            ['3.0.0', '4.0.0', true],
            ['3.0.0', '4.0.0-CMS-branch-123', true],
            ['1.0.0', '0.1.0', false],
            ['1.0.0', '0.0.1', false],
            ['2.1.0', '2.0.0', false],
            ['2.1.0-CMS-branch-123', '2.0.0', false],
        ];
    }

    /**
     * @dataProvider greaterThanProvider
     */
    public function testGreaterThan($newVersion, $currentVersion, $expectedResult)
    {
        $version = new VersionDiff('test', 'VERSION', $newVersion, $currentVersion);

        $this->assertSame($expectedResult, $version->isGreaterThan());
    }

    public function greaterThanProvider()
    {
        return [
            ['1.0.0', '1.0.0', false],
            ['3.0.0', '4.0.0-CMS-branch-123', false],
            ['3.0.0', '4.0.0', false],
            ['1.0.0', '0.1.0', true],
            ['1.0.0', '0.0.1', true],
            ['2.1.0', '2.0.0', true],
            ['2.1.0-CMS-branch-123', '2.0.0', true],
        ];
    }
}
