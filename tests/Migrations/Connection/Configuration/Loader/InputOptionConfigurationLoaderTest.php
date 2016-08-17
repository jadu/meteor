<?php

namespace Meteor\Migrations\Connection\Configuration\Loader;

use Mockery;

class InputOptionConfigurationLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $io;
    private $loader;

    public function setUp()
    {
        $this->io = Mockery::mock('Meteor\IO\IOInterface');
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

        $this->assertArraySubset(array('dbname' => 'test'), $this->loader->load('/path'));
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

        $this->assertArraySubset(array('user' => 'test'), $this->loader->load('/path'));
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

        $this->assertArraySubset(array('password' => 'test'), $this->loader->load('/path'));
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

        $this->assertArraySubset(array('host' => 'test'), $this->loader->load('/path'));
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

        $this->assertArraySubset(array('driver' => 'test'), $this->loader->load('/path'));
    }
}
