<?php

namespace Meteor\IO;

/**
 * @codeCoverageIgnore
 */
class NullIO implements IOInterface
{
    /**
     * {@inheritdoc}
     */
    public function isInteractive()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getArgument($name)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function hasArgument($name)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption($name)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function ask($question, $default = null)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function askConfirmation($question, $default = true)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function askAndHideAnswer($question)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function askChoice($question, $choices, $default = null)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function progressStart($max = 0)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function progressAdvance($step = 1)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function progressFinish()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function title($message)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function section($message)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function listing(array $elements)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function text($message)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function success($message)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function error($message)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function note($message)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function caution($message)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function table(array $headers, array $rows)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function writeln($messages)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = false)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function newLine($count = 1)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isVerbose()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isVeryVerbose()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isDebug()
    {
        return false;
    }
}
