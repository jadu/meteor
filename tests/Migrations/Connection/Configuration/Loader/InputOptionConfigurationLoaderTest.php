<?php

namespace Meteor\Migrations\Connection\Configuration\Loader;

use Meteor\IO\IOInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

class InputOptionConfigurationLoaderTest extends TestCase
{
    private $io;
    private $loader;

    protected function setUp(): void
    {
        $this->io = Mockery::mock(IOInterface::class);
        $this->loader = new InputOptionConfigurationLoader($this->io);
    }

    public function testLoadsDbName()
    {
        $this->io->shouldReceive('hasOption')
            ->with('db-name')
            ->andReturn(true);

        $this->io->shouldReceive('getOption')
            ->with('db-name')
            ->andReturn('test');

        $this->io->shouldReceive('hasOption')
            ->andReturn(false);

        $result = $this->loader->load('/path');

        static::assertArrayHasKey('dbname', $result);
        static::assertEquals($result['dbname'], 'test');
    }

    public function testLoadsUser()
    {
        $this->io->shouldReceive('hasOption')
            ->with('db-user')
            ->andReturn(true);

        $this->io->shouldReceive('getOption')
            ->with('db-user')
            ->andReturn('test');

        $this->io->shouldReceive('hasOption')
            ->andReturn(false);

        $result = $this->loader->load('/path');

        static::assertArrayHasKey('user', $result);
        static::assertEquals($result['user'], 'test');
    }

    public function testLoadsDbPassword()
    {
        $this->io->shouldReceive('hasOption')
            ->with('db-password')
            ->andReturn(true);

        $this->io->shouldReceive('getOption')
            ->with('db-password')
            ->andReturn('test');

        $this->io->shouldReceive('hasOption')
            ->andReturn(false);

        $result = $this->loader->load('/path');

        static::assertArrayHasKey('password', $result);
        static::assertEquals($result['password'], 'test');
    }

    public function testLoadsDbHost()
    {
        $this->io->shouldReceive('hasOption')
            ->with('db-host')
            ->andReturn(true);

        $this->io->shouldReceive('getOption')
            ->with('db-host')
            ->andReturn('test');

        $this->io->shouldReceive('hasOption')
            ->andReturn(false);

        $result = $this->loader->load('/path');

        static::assertArrayHasKey('host', $result);
        static::assertEquals($result['host'], 'test');
    }

    public function testLoadsDbPort()
    {
        $this->io->shouldReceive('hasOption')
            ->with('db-port')
            ->andReturn(true);

        $this->io->shouldReceive('getOption')
            ->with('db-port')
            ->andReturn('test');

        $this->io->shouldReceive('hasOption')
            ->andReturn(false);

        $result = $this->loader->load('/path');

        static::assertArrayHasKey('port', $result);
        static::assertEquals($result['port'], 'test');
    }

    public function testLoadDbDriver()
    {
        $this->io->shouldReceive('hasOption')
            ->with('db-driver')
            ->andReturn(true);

        $this->io->shouldReceive('getOption')
            ->with('db-driver')
            ->andReturn('test');

        $this->io->shouldReceive('hasOption')
            ->andReturn(false);

        $result = $this->loader->load('/path');

        static::assertArrayHasKey('driver', $result);
        static::assertEquals($result['driver'], 'test');
    }
}
