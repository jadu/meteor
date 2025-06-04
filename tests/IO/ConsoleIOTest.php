<?php

namespace Meteor\IO;

use Meteor\Logger\NullLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class ConsoleIOTest extends TestCase
{
    private $io;

    private $input;

    private $output;

    protected function setUp(): void
    {
        $this->input = new ArrayInput([]);
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
        static::assertSame(file_get_contents(__DIR__ . '/Fixtures/' . $fixture . '.txt'), $this->getOutput());
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
        $this->io->listing([
            'Item 1',
            'Item 2',
            'Item 3',
        ]);

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

        static::assertEmpty($this->getOutput());
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
        $this->io->table(['Name', 'Age', 'Favourite food'], [
            ['Tom', '30', 'Chocolate'],
            ['Lisa', '52', 'Strawberries'],
            ['Adam', '24', 'Bread'],
        ]);

        $this->assertOutputEquals('table');
    }

    public function testWriteln()
    {
        $this->io->writeln('Smelly');

        static::assertSame("Smelly\n", $this->getOutput());
    }

    public function testWrite()
    {
        $this->io->write('Smelly');

        static::assertSame('Smelly', $this->getOutput());
    }

    public function testNewLine()
    {
        $this->io->newLine();

        static::assertSame("\n", $this->getOutput());
    }

    /**
     * @dataProvider formatFileSizeProvider
     */
    public function testFormatFileSize($bytes, $dec, $expected)
    {
        $actual = $this->io->formatFileSize($bytes, $dec);

        static::assertEquals($expected, $actual);
    }

    public function formatFileSizeProvider()
    {
        return [
            ['1024', 2, '1.00 KiB'],
            ['1234', 2, '1.21 KiB'],
            ['123456789', 2, '117.74 MiB'],
            ['1048576', 2, '1.00 MiB'],
            ['1048576', 0, '1 MiB'],
            ['100456789012', 2, '93.56 GiB'],
        ];
    }
}
