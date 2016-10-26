<?php

namespace Meteor\Migrations\Connection\Configuration\Loader;

use Mockery;

class InputQuestionConfigurationLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $io;
    private $loader;

    public function setUp()
    {
        $this->io = Mockery::mock('Meteor\IO\IOInterface');
        $this->loader = new InputQuestionConfigurationLoader($this->io);
    }

    public function testAsksForDbNameWhenNotSet()
    {
        $this->io->shouldReceive('ask')
            ->andReturn('dbname')
            ->once();

        $configuration = [
            'dbname' => '',
            'user' => 'user',
            'password' => 'password',
            'host' => 'host',
            'driver' => 'driver',
        ];

        $this->assertArraySubset([
            'dbname' => 'dbname',
        ], $this->loader->load('install', $configuration));
    }

    public function testAsksForUserWhenNotSet()
    {
        $this->io->shouldReceive('ask')
            ->andReturn('user')
            ->once();

        $configuration = [
            'dbname' => 'dbname',
            'user' => '',
            'password' => 'password',
            'host' => 'host',
            'driver' => 'driver',
        ];

        $this->assertArraySubset([
            'user' => 'user',
        ], $this->loader->load('install', $configuration));
    }

    public function testAsksForPasswordWhenNotSet()
    {
        $this->io->shouldReceive('askAndHideAnswer')
            ->andReturn('password')
            ->once();

        $configuration = [
            'dbname' => 'dbname',
            'user' => 'user',
            'password' => '',
            'host' => 'host',
            'driver' => 'driver',
        ];

        $this->assertArraySubset([
            'password' => 'password',
        ], $this->loader->load('install', $configuration));
    }

    public function testAsksForHostWhenNotSet()
    {
        $this->io->shouldReceive('ask')
            ->andReturn('host')
            ->once();

        $configuration = [
            'dbname' => 'dbname',
            'user' => 'user',
            'password' => 'password',
            'host' => '',
            'driver' => 'driver',
        ];

        $this->assertArraySubset([
            'host' => 'host',
        ], $this->loader->load('install', $configuration));
    }

    public function testAsksForDriverWhenNotSet()
    {
        $this->io->shouldReceive('ask')
            ->andReturn('driver')
            ->once();

        $configuration = [
            'dbname' => 'dbname',
            'user' => 'user',
            'password' => 'password',
            'host' => 'host',
            'driver' => '',
        ];

        $this->assertArraySubset([
            'driver' => 'driver',
        ], $this->loader->load('install', $configuration));
    }
}
