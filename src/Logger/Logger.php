<?php

namespace Meteor\Logger;

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

class Logger implements LoggerInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var bool
     */
    private $enabled = false;

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function enable($path)
    {
        $this->enabled = true;
        $this->path = $path;

        $this->ensureDirectoryExists(dirname($path));
    }

    /**
     * @param string $dir
     */
    private function ensureDirectoryExists($dir)
    {
        if (!is_dir($dir)) {
            if (file_exists($dir)) {
                throw new RuntimeException(sprintf('"%s" exists and is not a directory.', $dir));
            }

            // NB: Cannot pass Filesystem in as a dependency of this class due to a circular reference
            $filesystem = new Filesystem();
            $filesystem->mkdir($dir);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * {@inheritdoc}
     */
    public function log($messages)
    {
        if (!$this->enabled) {
            return;
        }

        $lines = $this->splitMessageLines($messages);
        $lines = $this->formatLines($lines);

        file_put_contents($this->path, implode("\n", $lines)."\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * @param array|string $messages
     *
     * @return array
     */
    private function splitMessageLines($messages)
    {
        $lines = [];

        if (!is_array($messages)) {
            $messages = [$messages];
        }

        foreach ($messages as $message) {
            $lines = array_merge($lines, explode("\n", $message));
        }

        $lines = array_values($lines);

        return $lines;
    }

    /**
     * @param array $lines
     *
     * @return array
     */
    private function formatLines(array $lines)
    {
        $lineCount = count($lines);
        for ($i = 0; $i < $lineCount; ++$i) {
            $lines[$i] = $this->stripColourFormatting($lines[$i]);
            $lines[$i] = $this->prependTimestamp($lines[$i]);
            $lines[$i] = $this->stripTrailingWhitespace($lines[$i]);
        }

        return $lines;
    }

    /**
     * @param string $line
     *
     * @return string
     */
    private function stripColourFormatting($line)
    {
        // Taken from `Symfony\Component\Console\Formatter\OutoutFormatter::format()`
        $tagRegex = '[a-z][a-z0-9_=;-]*';

        return preg_replace("#<(($tagRegex) | /($tagRegex)?)>#ix", '', $line);
    }

    /**
     * @param string $line
     *
     * @return string
     */
    private function stripTrailingWhitespace($line)
    {
        return trim($line);
    }

    /**
     * @param string $line
     *
     * @return string
     */
    private function prependTimestamp($line)
    {
        return '['.date('c').'/'.round(memory_get_usage() / 1048576, 2).'MB] '.$line;
    }
}
