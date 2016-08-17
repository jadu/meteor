<?php

namespace Meteor\Platform\Unix;

class InstallConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetWebUserFallsBackToApacheUserWhenNotSuexec()
    {
        $config = new InstallConfig(array(
            'SUEXEC' => 'no',
            'APACHE_USER' => 'apache',
        ));

        $this->assertSame('apache', $config->getWebUser());
    }

    public function testGetWebGroupFallsBackToApacheGroupWhenNotSuexec()
    {
        $config = new InstallConfig(array(
            'SUEXEC' => 'no',
            'APACHE_GROUP' => 'apache',
        ));

        $this->assertSame('apache', $config->getWebGroup());
    }

    /**
     * @dataProvider suexecValueProvider
     */
    public function testIsSuexec($value, $expectedResult)
    {
        $config = new InstallConfig(array(
            'SUEXEC' => $value,
        ));

        $this->assertSame($expectedResult, $config->isSuexec());
    }

    public function suexecValueProvider()
    {
        return array(
            array('', false),
            array('n', false),
            array('y', false),
            array('yes', true),
        );
    }
}
