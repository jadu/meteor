<?php

namespace Meteor\Migrations\Configuration;

use Mockery;

class DatabaseConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensure the method exists as it is used by old migrations.
     */
    public function testGetSetJaduPath()
    {
        $configuration = new DatabaseConfiguration(Mockery::mock('Doctrine\DBAL\Connection'));
        $configuration->setJaduPath('/var/www/jadu');

        $this->assertSame('/var/www/jadu', $configuration->getJaduPath());
    }
}
