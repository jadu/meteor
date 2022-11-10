<?php

namespace Meteor\Platform\Unix;

use PHPUnit\Framework\TestCase;

class InstallConfigTest extends TestCase
{
    public function testGetWebUserFallsBackToApacheUserWhenNotSuexec()
    {
        $config = new InstallConfig([
            'SUEXEC' => 'no',
            'APACHE_USER' => 'apache',
        ]);

        static::assertSame('apache', $config->getWebUser());
    }

    public function testGetWebGroupFallsBackToApacheGroupWhenNotSuexec()
    {
        $config = new InstallConfig([
            'SUEXEC' => 'no',
            'APACHE_GROUP' => 'apache',
        ]);

        static::assertSame('apache', $config->getWebGroup());
    }

    /**
     * @dataProvider suexecValueProvider
     */
    public function testIsSuexec($value, $expectedResult)
    {
        $config = new InstallConfig([
            'SUEXEC' => $value,
        ]);

        static::assertSame($expectedResult, $config->isSuexec());
    }

    public function suexecValueProvider()
    {
        return [
            ['', false],
            ['n', false],
            ['y', false],
            ['yes', true],
        ];
    }
}
