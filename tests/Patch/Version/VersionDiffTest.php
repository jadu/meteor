<?php

namespace Meteor\Patch\Version;

class VersionDiffTest extends \PHPUnit_Framework_TestCase
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
        return array(
            array('1.0.0', '1.0.0', false),
            array('3.0.0', '4.0.0', true),
            array('3.0.0', '4.0.0-CMS-branch-123', true),
            array('1.0.0', '0.1.0', false),
            array('1.0.0', '0.0.1', false),
            array('2.1.0', '2.0.0', false),
            array('2.1.0-CMS-branch-123', '2.0.0', false),
        );
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
        return array(
            array('1.0.0', '1.0.0', false),
            array('3.0.0', '4.0.0-CMS-branch-123', false),
            array('3.0.0', '4.0.0', false),
            array('1.0.0', '0.1.0', true),
            array('1.0.0', '0.0.1', true),
            array('2.1.0', '2.0.0', true),
            array('2.1.0-CMS-branch-123', '2.0.0', true),
        );
    }
}
