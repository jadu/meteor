<?php

namespace Meteor\Logger;

use org\bovigo\vfs\vfsStream;

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    private $logger;

    public function setUp()
    {
        $this->logger = new Logger();

        vfsStream::setup('root');
    }

    public function testEnableCreatesLogDirectory()
    {
        $this->logger->enable(vfsStream::url('root/logs/meteor.log'));

        $this->assertTrue(is_dir(vfsStream::url('root/logs')));
    }

    public function testLogWritesToFileWhenEnabled()
    {
        $this->logger->enable(vfsStream::url('root/logs/meteor.log'));

        $this->logger->log('test');

        $this->assertContains('test', file_get_contents(vfsStream::url('root/logs/meteor.log')));
    }

    public function testLogWritesToFileWithMultipleMessages()
    {
        $this->logger->enable(vfsStream::url('root/logs/meteor.log'));

        $this->logger->log(['test', 'hello', 'goodbye', '']);

        $expected = <<<'LOG'
[2016-07-01T08:00:00+00:00/32.76MB] test
[2016-07-01T08:00:00+00:00/32.76MB] hello
[2016-07-01T08:00:00+00:00/32.76MB] goodbye
[2016-07-01T08:00:00+00:00/32.76MB]
LOG;

        $this->assertContains($expected, file_get_contents(vfsStream::url('root/logs/meteor.log')));
    }

    public function testLogWritesMultipleSeparateMessagesCorrectly()
    {
        $this->logger->enable(vfsStream::url('root/logs/meteor.log'));

        $this->logger->log('test');
        $this->logger->log('hello');
        $this->logger->log('goodbye');
        $this->logger->log('');

        $expected = <<<'LOG'
[2016-07-01T08:00:00+00:00/32.76MB] test
[2016-07-01T08:00:00+00:00/32.76MB] hello
[2016-07-01T08:00:00+00:00/32.76MB] goodbye
[2016-07-01T08:00:00+00:00/32.76MB]
LOG;

        $this->assertContains($expected, file_get_contents(vfsStream::url('root/logs/meteor.log')));
    }

    public function testLogAddsTimestampToEveryLineInMultilineMessage()
    {
        $this->logger->enable(vfsStream::url('root/logs/meteor.log'));

        $this->logger->log("this\nis\na\ntest");

        $expected = <<<'LOG'
[2016-07-01T08:00:00+00:00/32.76MB] this
[2016-07-01T08:00:00+00:00/32.76MB] is
[2016-07-01T08:00:00+00:00/32.76MB] a
[2016-07-01T08:00:00+00:00/32.76MB] test
LOG;

        $this->assertContains($expected, file_get_contents(vfsStream::url('root/logs/meteor.log')));
    }

    public function testLogStripsColourFormatting()
    {
        $this->logger->enable(vfsStream::url('root/logs/meteor.log'));

        $this->logger->log('<error>Error</>');

        $expected = <<<'LOG'
[2016-07-01T08:00:00+00:00/32.76MB] Error
LOG;

        $this->assertContains($expected, file_get_contents(vfsStream::url('root/logs/meteor.log')));
    }

    public function testLogStripsTrailingWhitespace()
    {
        $this->logger->enable(vfsStream::url('root/logs/meteor.log'));

        $this->logger->log('test     ');

        $expected = <<<'LOG'
[2016-07-01T08:00:00+00:00/32.76MB] test
LOG;

        $this->assertContains($expected, file_get_contents(vfsStream::url('root/logs/meteor.log')));
    }

    public function testLogDoesNotWriteToFileWhenDisabled()
    {
        $this->logger->enable(vfsStream::url('root/logs/meteor.log'));
        $this->logger->disable();

        $this->logger->log('test');

        $this->assertFalse(file_exists(vfsStream::url('root/logs/meteor.log')));
    }
}

/**
 * Stub the date function so the logger always adds the same timestamp.
 */
function date($format)
{
    return \date($format, 1467360000);
}

function memory_get_usage()
{
    return 34355432;
}
