<?php

namespace Meteor\Patch\Task;

use Mockery;

class CheckDatabaseConnectionHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $connectionFactory;
    private $handler;

    public function setUp()
    {
        $this->connectionFactory = Mockery::mock('Meteor\Migrations\Connection\ConnectionFactory');
        $this->handler = new CheckDatabaseConnectionHandler($this->connectionFactory);
    }

    public function testChecksConnectionWhenRootPackageHasMigrations()
    {
        $config = array(
            'migrations' => array(
                'table' => 'migrations',
            ),
            'combined' => array(),
        );

        $this->connectionFactory->shouldReceive('getConnection')
            ->with('install')
            ->andReturn(Mockery::mock('Doctrine\DBAL\Connection'))
            ->once();

        $this->handler->handle(new CheckDatabaseConnection('install'), $config);
    }

    public function testChecksConnectionWhenCombinedPackagesHaveMigrations()
    {
        $config = array(
            'combined' => array(
                array(
                    'migrations' => array(
                        'table' => 'migrations',
                    ),
                ),
            ),
        );

        $this->connectionFactory->shouldReceive('getConnection')
            ->with('install')
            ->andReturn(Mockery::mock('Doctrine\DBAL\Connection'))
            ->once();

        $this->handler->handle(new CheckDatabaseConnection('install'), $config);
    }

    public function testDoesNotCheckConnectionIfNoMigrations()
    {
        $config = array(
            'combined' => array(),
        );

        $this->connectionFactory->shouldReceive('getConnection')
            ->never();

        $this->handler->handle(new CheckDatabaseConnection('install'), $config);
    }
}
