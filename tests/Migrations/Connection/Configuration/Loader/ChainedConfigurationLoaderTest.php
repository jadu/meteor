<?php

namespace Meteor\Migrations\Connection\Configuration\Loader;

use Mockery;
use PHPUnit\Framework\TestCase;

class ChainedConfigurationLoaderTest extends TestCase
{
    public function testFirstLoaderTakesPrecedence()
    {
        $loader1 = Mockery::mock('Meteor\Migrations\Connection\Configuration\Loader\ConfigurationLoaderInterface', [
            'load' => [
                'dbname' => 'db1',
                'user' => 'user1',
                'password' => 'password1',
                'host' => 'host1',
                'driver' => 'driver1',
            ],
        ]);
        $loader2 = Mockery::mock('Meteor\Migrations\Connection\Configuration\Loader\ConfigurationLoaderInterface', [
            'load' => [
                'dbname' => 'db2',
                'user' => 'user2',
                'password' => 'password2',
                'host' => 'host2',
                'driver' => 'driver2',
            ],
        ]);
        $loader3 = Mockery::mock('Meteor\Migrations\Connection\Configuration\Loader\ConfigurationLoaderInterface', [
            'load' => [
                'dbname' => 'db3',
                'user' => 'user3',
                'password' => 'password3',
                'host' => 'host3',
                'driver' => 'driver3',
            ],
        ]);

        $chainedLoader = new ChainedConfigurationLoader([$loader1, $loader2, $loader3]);

        static::assertSame([
                'dbname' => 'db1',
                'user' => 'user1',
                'password' => 'password1',
                'host' => 'host1',
                'driver' => 'driver1',
        ], $chainedLoader->load('/path', []));
    }

    public function testIgnoresEmptyValues()
    {
        $loader1 = Mockery::mock('Meteor\Migrations\Connection\Configuration\Loader\ConfigurationLoaderInterface', [
            'load' => [
                'dbname' => '',
                'user' => 'user1',
                'password' => '',
                'host' => 'host1',
                'driver' => '',
            ],
        ]);
        $loader2 = Mockery::mock('Meteor\Migrations\Connection\Configuration\Loader\ConfigurationLoaderInterface', [
            'load' => [
                'dbname' => 'db2',
                'user' => 'user2',
                'password' => '',
                'host' => 'host2',
                'driver' => 'driver2',
            ],
        ]);
        $loader3 = Mockery::mock('Meteor\Migrations\Connection\Configuration\Loader\ConfigurationLoaderInterface', [
            'load' => [
                'dbname' => 'db3',
                'user' => 'user3',
                'password' => 'password3',
                'host' => 'host3',
                'driver' => 'driver3',
            ],
        ]);

        $chainedLoader = new ChainedConfigurationLoader([$loader1, $loader2, $loader3]);

        static::assertSame([
                'dbname' => 'db2',
                'user' => 'user1',
                'password' => 'password3',
                'host' => 'host1',
                'driver' => 'driver2',
        ], $chainedLoader->load('/path', []));
    }
}
