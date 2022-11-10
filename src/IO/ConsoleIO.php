<?php

namespace Meteor\IO;

use Meteor\Logger\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Terminal;

class ConsoleIO implements IOInterface
{
    const MAX_LINE_LENGTH = 120;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var BufferedOutput
     */
    private $bufferedOutput;

    /**
     * @var int
     */
    private $lineLength;

    /**
     * @var ProgressBar|null
     */
    private $progressBar;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param HelperSet $helperSet
     */
    public function __construct(InputInterface $input, OutputInterface $output, LoggerInterface $logger)
    {
        $this->input = $input;
        $this->output = $output;
        $this->logger = $logger;

        $this->bufferedOutput = new BufferedOutput($output->getVerbosity(), false, clone $output->getFormatter());
        // Windows cmd wraps lines as soon as the terminal width is reached, whether there are following chars or not.
        $this->lineLength = min($this->getTerminalWidth() - (int) (DIRECTORY_SEPARATOR === '\\'), self::MAX_LINE_LENGTH);
    }

    /**
     * {@inheritdoc}
     */
    public function isInteractive()
    {
        return $this->input->isInteractive();
    }

    /**
     * {@inheritdoc}
     */
    public function getArgument($name)
    {
        return $this->input->getArgument($name);
    }

    /**
     * {@inheritdoc}
     */
    public function hasArgument($name)
    {
        return $this->input->hasArgument($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name)
    {
        return $this->input->getOption($name) ?: '';
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->input->getOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption($name)
    {
        return $this->input->hasOption($name);
    }

    /**
     * {@inheritdoc}
     */
    public function ask($question, $default = null)
    {
        $question = sprintf(" <info>%s</info>%s:\n > ", $question, $default !== null ? ' [<comment>' . $default . '</>]' : '');
        $question = new Question($question, $default);

        return $this->askQuestion($question);
    }

    /**
     * {@inheritdoc}
     */
    public function askConfirmation($question, $default = true)
    {
        $question = sprintf(" <info>%s (yes/no)</info> [<comment>%s</comment>]:\n > ", $question, $default ? 'yes' : 'no');
        $question = new ConfirmationQuestion($question, $default);

        return $this->askQuestion($question);
    }

    /**
     * {@inheritdoc}
     */
    public function askAndHideAnswer($question)
    {
        $question = sprintf(" <info>%s</info>:\n > ", $question);
        $question = new Question($question);
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            $question->setHidden(true);
        }

        return $this->askQuestion($question);
    }

    /**
     * {@inheritdoc}
     */
    public function askChoice($question, $choices, $default = null)
    {
        $question = sprintf(" <info>%s</info> [<comment>%s</comment>]:\n > ", $question, $choices[$default] ?? null);
        $question = new ChoiceQuestion($question, $choices, $default);

        return $this->askQuestion($question);
    }

    private function askQuestion(Question $question)
    {
        if ($this->input->isInteractive()) {
            $this->autoPrependBlock();
        }

        $helper = new QuestionHelper();
        $answer = $helper->ask($this->input, $this->output, $question);

        if ($this->input->isInteractive()) {
            $this->newLine();
            $this->bufferedOutput->write("\n");
        }

        return $answer;
    }

    /**
     * {@inheritdoc}
     */
    public function progressStart($max = 0)
    {
        $this->progressBar = $this->createProgressBar($max);
        $this->progressBar->setRedrawFrequency(ceil($max / 100));
        $this->progressBar->start();
    }

    /**
     * {@inheritdoc}
     */
    public function progressAdvance($step = 1)
    {
        $this->getProgressBar()->advance($step);
    }

    /**
     * {@inheritdoc}
     */
    public function progressFinish()
    {
        $this->getProgressBar()->finish();
        $this->progressBar = null;

        $this->newLine(2);
    }

    /**
     * @return ProgressBar
     */
    private function getProgressBar()
    {
        if (!$this->progressBar) {
            throw new RuntimeException('The progress bar is not started.');
        }

        return $this->progressBar;
    }

    /**
     * {@inheritdoc}
     */
    public function createProgressBar($max = 0)
    {
        $progressBar = new ProgressBar($this->output, $max);

        if ('\\' !== DIRECTORY_SEPARATOR) {
            $progressBar->setEmptyBarCharacter('░'); // light shade character \u2591
            $progressBar->setProgressCharacter('');
            $progressBar->setBarCharacter('▓'); // dark shade character \u2593
        }

        return $progressBar;
    }

    /**
     * Formats a message as a block of text.
     *
     * @param string|array $message The message to write in the block
     * @param string|null $type The block type (added in [] on first line)
     * @param string|null $style The style to apply to the whole block
     * @param string $prefix The prefix for the block
     * @param bool $padding Whether to add vertical padding
     */
    private function block($message, $type = null, $style = null, $prefix = ' ', $padding = false)
    {
        $messages = is_array($message) ? array_values($message) : [$message];

        $this->autoPrependBlock();
        $this->writeln($this->createBlock($messages, $type, $style, $prefix, $padding, true));
        $this->newLine();
    }

    /**
     * {@inheritdoc}
     */
    public function title($message)
    {
        $this->autoPrependBlock();
        $this->writeln([
            sprintf('<comment>%s</>', $message),
            sprintf('<comment>%s</>', str_repeat('=', Helper::strlenWithoutDecoration($this->output->getFormatter(), $message))),
        ]);
        $this->newLine();
    }

    /**
     * {@inheritdoc}
     */
    public function section($message)
    {
        $this->autoPrependBlock();
        $this->writeln([
            sprintf('<comment>%s</>', $message),
            sprintf('<comment>%s</>', str_repeat('-', Helper::strlenWithoutDecoration($this->output->getFormatter(), $message))),
        ]);
        $this->newLine();
    }

    /**
     * {@inheritdoc}
     */
    public function listing(array $elements)
    {
        $this->autoPrependText();
        $elements = array_map(function ($element) {
            return sprintf(' * %s', $element);
        }, $elements);

        $this->writeln($elements);
        $this->newLine();
    }

    /**
     * {@inheritdoc}
     */
    public function text($message)
    {
        $this->autoPrependText();

        $messages = is_array($message) ? array_values($message) : [$message];
        foreach ($messages as $message) {
            $this->writeln(sprintf(' %s', $message));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message)
    {
        $messages = is_array($message) ? array_values($message) : [$message];
        $block = $this->createBlock($messages, null, null, '<fg=magenta;bg=default>-- </>');

        if ($this->isDebug()) {
            $this->writeln($block);
        } else {
            $this->logger->log($block);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function success($message)
    {
        $this->block($message, 'OK', 'fg=black;bg=green', ' ', true);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message)
    {
        $this->block($message, 'ERROR', 'fg=white;bg=red', ' ', true);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message)
    {
        $this->block($message, 'WARNING', 'fg=white;bg=red', ' ', true);
    }

    /**
     * {@inheritdoc}
     */
    public function note($message)
    {
        $this->block($message, 'NOTE', 'fg=yellow', ' ! ');
    }

    /**
     * {@inheritdoc}
     */
    public function caution($message)
    {
        $this->block($message, 'CAUTION', 'fg=white;bg=red', ' ! ', true);
    }

    /**
     * {@inheritdoc}
     */
    public function table(array $headers, array $rows)
    {
        $table = new Table($this->output);
        $table->setHeaders($headers);
        $table->setRows($rows);

        $style = new TableStyle();
        $style->setCellHeaderFormat('<info>%s</info>');
        $style->setHorizontalBorderChars('-');
        $style->setVerticalBorderChars(' ');
        $style->setCrossingChars(' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ');
        $table->setStyle($style);

        $table->render();
        $this->newLine();
    }

    /**
     * {@inheritdoc}
     */
    public function writeln($messages)
    {
        $this->output->writeln($messages);
        $this->bufferedOutput->writeln($this->reduceBuffer($messages));

        $this->logger->log($messages);
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = false)
    {
        $this->output->write($messages, $newline);
        $this->bufferedOutput->write($this->reduceBuffer($messages), $newline);

        $this->logger->log($messages);
    }

    /**
     * {@inheritdoc}
     */
    public function newLine($count = 1)
    {
        $this->output->write(str_repeat(PHP_EOL, $count));
        $this->bufferedOutput->write(str_repeat("\n", $count));
    }

    /**
     * {@inheritdoc}
     */
    public function isVerbose()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * {@inheritdoc}
     */
    public function isVeryVerbose()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE;
    }

    /**
     * {@inheritdoc}
     */
    public function isDebug()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG;
    }

    public function setLineLength($lineLength)
    {
        $this->lineLength = $lineLength;
    }

    private function getTerminalWidth()
    {
        return (new Terminal())->getWidth();
    }

    private function autoPrependBlock()
    {
        $chars = substr(str_replace(PHP_EOL, "\n", $this->bufferedOutput->fetch()), -2);

        if (!isset($chars[0])) {
            return $this->newLine(); //empty history, so we should start with a new line.
        }
        //Prepend new line for each non LF chars (This means no blank line was output before)
        $this->newLine(2 - substr_count($chars, "\n"));
    }

    private function autoPrependText()
    {
        $fetched = $this->bufferedOutput->fetch();
        //Prepend new line if last char isn't EOL:
        if ("\n" !== substr($fetched, -1)) {
            $this->newLine();
        }
    }

    private function reduceBuffer($messages)
    {
        // We need to know if the two last chars are PHP_EOL
        // Preserve the last 4 chars inserted (PHP_EOL on windows is two chars) in the history buffer
        return array_map(function ($value) {
            return substr($value, -4);
        }, array_merge([$this->bufferedOutput->fetch()], (array) $messages));
    }

    private function createBlock($messages, $type = null, $style = null, $prefix = ' ', $padding = false, $escape = false)
    {
        $indentLength = 0;
        $lineIndentation = '';
        $prefixLength = Helper::strlenWithoutDecoration($this->output->getFormatter(), $prefix);
        $lines = [];

        if (null !== $type) {
            $type = sprintf('[%s] ', $type);
            $indentLength = strlen($type);
            $lineIndentation = str_repeat(' ', $indentLength);
        }

        // wrap and add newlines for each element
        foreach ($messages as $key => $message) {
            if ($escape) {
                $message = OutputFormatter::escape($message);
            }

            $lines = array_merge($lines, explode(PHP_EOL, wordwrap($message, $this->lineLength - $prefixLength - $indentLength, PHP_EOL, true)));

            if (count($messages) > 1 && $key < count($messages) - 1) {
                $lines[] = '';
            }
        }

        foreach ($lines as $i => &$line) {
            if (null !== $type) {
                $line = 0 === $i ? $type . $line : $lineIndentation . $line;
            }

            $line = $prefix . $line;
            $line .= str_repeat(' ', $this->lineLength - Helper::strlenWithoutDecoration($this->output->getFormatter(), $line));

            if ($style) {
                $line = sprintf('<%s>%s</>', $style, $line);
            }
        }

        if ($padding && $this->output->isDecorated()) {
            array_unshift($lines, '');
            $lines[] = '';
        }

        return $lines;
    }
}
