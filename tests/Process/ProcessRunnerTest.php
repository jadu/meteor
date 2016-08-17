<?php

namespace Meteor\Process;

use Meteor\IO\NullIO;
use RuntimeException;

class ProcessRunnerTest extends \PHPUnit_Framework_TestCase
{
    private $processRunner;

    public function setUp()
    {
        $this->processRunner = new ProcessRunner(new NullIO());
    }

    public function testRunReturnsOutputWhenSuccessful()
    {
        $output = $this->processRunner->run('whoami');

        $this->assertNotEmpty($output);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testRunThrowsExceptionWithErrorOutputWhenNotSuccessful()
    {
        $this->processRunner->run('invalidcommand');
    }
}
