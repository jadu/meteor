<?php

namespace Meteor\IO;

use Meteor\Logger\NullLogger;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class ConsoleIOTest extends \PHPUnit_Framework_TestCase
{
    private $io;

    public function setUp()
    {
        $this->input = new ArrayInput(array());
        $this->input->setInteractive(true);

        $this->output = new StreamOutput(fopen('php://memory', 'w', false));

        // Turn off colour output for testing
        $this->output->setDecorated(false);

        $this->io = new ConsoleIO($this->input, $this->output, new NullLogger());
        $this->io->setLineLength(ConsoleIO::MAX_LINE_LENGTH);
    }

    private function getOutput()
    {
        rewind($this->output->getStream());

        return stream_get_contents($this->output->getStream());
    }

    private function assertOutputEquals($fixture)
    {
        $this->assertSame(file_get_contents(__DIR__.'/Fixtures/'.$fixture.'.txt'), $this->getOutput());
    }

    public function testTitle()
    {
        $this->io->title('This is a title');

        $this->assertOutputEquals('title');
    }

    public function testSection()
    {
        $this->io->section('This is a section');

        $this->assertOutputEquals('section');
    }

    public function testListing()
    {
        $this->io->listing(array(
            'Item 1',
            'Item 2',
            'Item 3',
        ));

        $this->assertOutputEquals('listing');
    }

    public function testText()
    {
        $this->io->text('This is some text');

        $this->assertOutputEquals('text');
    }

    public function testDebugOutputWhenDebugVerbosity()
    {
        $this->output->setVerbosity(StreamOutput::VERBOSITY_DEBUG);

        $this->io->debug('This is some text');

        $this->assertOutputEquals('debug');
    }

    public function testDebugOutputsNothingWhenNotDebugVerbosity()
    {
        $this->output->setVerbosity(StreamOutput::VERBOSITY_NORMAL);

        $this->io->debug('This is some text');

        $this->assertEmpty($this->getOutput());
    }

    public function testSuccess()
    {
        $this->io->success('This is a success');

        $this->assertOutputEquals('success');
    }

    public function testError()
    {
        $this->io->error('This is an error');

        $this->assertOutputEquals('error');
    }

    public function testWarning()
    {
        $this->io->warning('This is a warning');

        $this->assertOutputEquals('warning');
    }

    public function testNote()
    {
        $this->io->note('This is a note');

        $this->assertOutputEquals('note');
    }

    public function testCaution()
    {
        $this->io->caution('This is a caution');

        $this->assertOutputEquals('caution');
    }

    public function testTable()
    {
        $this->io->table(array('Name', 'Age', 'Favourite food'), array(
            array('Tom', '30', 'Chocolate'),
            array('Lisa', '52', 'Strawberries'),
            array('Adam', '24', 'Bread'),
        ));

        $this->assertOutputEquals('table');
    }

    public function testWriteln()
    {
        $this->io->writeln('Smelly');

        $this->assertSame("Smelly\n", $this->getOutput());
    }

    public function testWrite()
    {
        $this->io->write('Smelly');

        $this->assertSame('Smelly', $this->getOutput());
    }

    public function testNewLine()
    {
        $this->io->newLine();

        $this->assertSame("\n", $this->getOutput());
    }
}
