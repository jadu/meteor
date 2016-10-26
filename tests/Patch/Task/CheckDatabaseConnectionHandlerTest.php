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
        $config = [
            'migrations' => [
                'table' => 'migrations',
            ],
            'combined' => [],
        ];

        $this->connectionFactory->shouldReceive('getConnection')
            ->with('install')
            ->andReturn(Mockery::mock('Doctrine\DBAL\Connection'))
            ->once();

        $this->handler->handle(new CheckDatabaseConnection('install'), $config);
    }

    public function testChecksConnectionWhenCombinedPackagesHaveMigrations()
    {
        $config = [
            'combined' => [
                [
                    'migrations' => [
                        'table' => 'migrations',
                    ],
                ],
            ],
        ];

        $this->connectionFactory->shouldReceive('getConnection')
            ->with('install')
            ->andReturn(Mockery::mock('Doctrine\DBAL\Connection'))
            ->once();

        $this->handler->handle(new CheckDatabaseConnection('install'), $config);
    }

    public function testDoesNotCheckConnectionIfNoMigrations()
    {
        $config = [
            'combined' => [],
        ];

        $this->connectionFactory->shouldReceive('getConnection')
            ->never();

        $this->handler->handle(new CheckDatabaseConnection('install'), $config);
    }
}
