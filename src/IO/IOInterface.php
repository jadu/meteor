<?php

namespace Meteor\IO;

interface IOInterface
{
    /**
     * Is this input means interactive?
     *
     * @return bool
     */
    public function isInteractive();

    /**
     * Gets argument by name.
     *
     * @param string $name The name of the argument
     *
     * @return mixed
     */
    public function getArgument($name);

    /**
     * Returns true if an InputArgument object exists by name or position.
     *
     * @param string|int $name The InputArgument name or position
     *
     * @return bool true if the InputArgument object exists, false otherwise
     */
    public function hasArgument($name);

    /**
     * Gets an option by name.
     *
     * @param string $name The name of the option
     *
     * @return mixed
     */
    public function getOption($name);

    /**
     * Gets all options.
     *
     * @return array
     */
    public function getOptions();

    /**
     * Returns true if an InputOption object exists by name.
     *
     * @param string $name The InputOption name
     *
     * @return bool true if the InputOption object exists, false otherwise
     */
    public function hasOption($name);

    /**
     * Asks a question to the user.
     *
     * @param string|array $question The question to ask
     * @param string $default The default answer if none is given by the user
     *
     * @return string The user answer
     *
     * @throws \RuntimeException If there is no data to read in the input stream
     */
    public function ask($question, $default = null);

    /**
     * Asks a confirmation to the user.
     *
     * The question will be asked until the user answers by nothing, yes, or no.
     *
     * @param string|array $question The question to ask
     * @param bool $default The default answer if the user enters nothing
     *
     * @return bool true if the user has confirmed, false otherwise
     */
    public function askConfirmation($question, $default = true);

    /**
     * Asks a question to the user and hide the answer.
     *
     * @param string $question The question to ask
     *
     * @return string The answer
     */
    public function askAndHideAnswer($question);

    /**
     * Asks a choice question.
     *
     * @param string $question The question the ask
     * @param array $choices The choices
     * @param string $default
     *
     * @return mixed
     */
    public function askChoice($question, $choices, $default = null);

    /**
     * Starts a progress bar.
     *
     * @param int $max
     */
    public function progressStart($max = 0);

    /**
     * Advances the progress bar.
     *
     * @param int $step
     */
    public function progressAdvance($step = 1);

    /**
     * Stops the progress bar.
     */
    public function progressFinish();

    /**
     * @param string|array $message
     */
    public function title($message);

    /**
     * @param string|array $message
     */
    public function section($message);

    /**
     * @param array $elements
     */
    public function listing(array $elements);

    /**
     * @param string|array $message
     */
    public function text($message);

    /**
     * @param string|array $message
     */
    public function debug($message);

    /**
     * @param string|array $message
     */
    public function success($message);

    /**
     * @param string|array $message
     */
    public function error($message);

    /**
     * @param string|array $message
     */
    public function warning($message);

    /**
     * @param string|array $message
     */
    public function note($message);

    /**
     * @param string|array $message
     */
    public function caution($message);

    /**
     * @param array $headers
     * @param array $rows
     */
    public function table(array $headers, array $rows);

    /**
     * @param string|array $message
     */
    public function writeln($messages);

    /**
     * @param string|array $message
     * @param bool $newline
     */
    public function write($messages, $newline = false);

    /**
     * @param int $count
     */
    public function newLine($count = 1);

    /**
     * Is this output verbose?
     *
     * @return bool
     */
    public function isVerbose();

    /**
     * Is the output very verbose?
     *
     * @return bool
     */
    public function isVeryVerbose();

    /**
     * Is the output in debug verbosity?
     *
     * @return bool
     */
    public function isDebug();
}
