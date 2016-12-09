<?php

namespace Meteor;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class ApplicationFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $application;
    private $output;

    public function setUp()
    {
        $factory = new ApplicationFactory();
        $this->application = $factory->createApplication(new ClassLoader());
        $this->application->setAutoExit(false);

        $this->output = new StreamOutput(fopen('php://memory', 'w', false));
    }

    public function testCanRunPatchApplyCommand()
    {
        // Attempt to run the patch:apply command
        $input = new ArrayInput([
            'command' => 'patch:apply',
            '--working-dir' => __DIR__,
            // NB: Intentionally omitted the --install-dir option
            //  so that the command does not properly execute during tests.
        ]);

        $input->setInteractive(false);

        $this->application->run($input, $this->output);

        $this->assertRegExp('/Missing the required --install-dir option/', $this->getDisplay());
    }

    private function getDisplay()
    {
        rewind($this->output->getStream());

        $display = stream_get_contents($this->output->getStream());
        $display = str_replace(PHP_EOL, "\n", $display);

        return $display;
    }
}
