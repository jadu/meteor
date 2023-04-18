<?php

namespace Meteor\Migrations\Connection\Configuration\Loader;

use Mockery;
use PHPUnit\Framework\TestCase;

class InputQuestionConfigurationLoaderTest extends TestCase
{
    private $io;
    private $loader;

    protected function setUp(): void
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
            'port' => 'port',
            'driver' => 'driver',
        ];

        $result = $this->loader->load('install', $configuration);

        static::assertArrayHasKey('dbname', $result);
        static::assertEquals($result['dbname'], 'dbname');
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
            'port' => 'port',
            'driver' => 'driver',
        ];

        $result = $this->loader->load('install', $configuration);

        static::assertArrayHasKey('user', $result);
        static::assertEquals($result['user'], 'user');
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
            'port' => 'port',
            'driver' => 'driver',
        ];

        $result = $this->loader->load('install', $configuration);

        static::assertArrayHasKey('password', $result);
        static::assertEquals($result['password'], 'password');
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
            'port' => 'port',
            'driver' => 'driver',
        ];

        $result = $this->loader->load('install', $configuration);

        static::assertArrayHasKey('host', $result);
        static::assertEquals($result['host'], 'host');
    }

    public function testDoesNotAskForPortWhenNotSet()
    {
        $this->io->shouldReceive('ask')
            ->never();

        $configuration = [
            'dbname' => 'dbname',
            'user' => 'user',
            'password' => 'password',
            'host' => 'host',
            'driver' => 'driver',
        ];

        $result = $this->loader->load('install', $configuration);

        static::assertArrayNotHasKey('port', $result);
    }

    public function testDoesNotAskForPortWhenBlank()
    {
        $this->io->shouldReceive('ask')
            ->never();

        $configuration = [
            'dbname' => 'dbname',
            'user' => 'user',
            'password' => 'password',
            'host' => 'host',
            'port' => '',
            'driver' => 'driver',
        ];

        $result = $this->loader->load('install', $configuration);

        static::assertArrayHasKey('port', $result);
        static::assertEquals($result['port'], '');
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
            'port' => 'port',
            'driver' => '',
        ];

        $result = $this->loader->load('install', $configuration);

        static::assertArrayHasKey('driver', $result);
        static::assertEquals($result['driver'], 'driver');
    }
}
