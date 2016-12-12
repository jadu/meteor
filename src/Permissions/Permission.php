<?php

namespace Meteor\Permissions;

use Symfony\Component\Finder\Glob;

class Permission
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var bool
     */
    protected $read;

    /**
     * @var bool
     */
    protected $write;

    /**
     * @var bool
     */
    protected $execute;

    /**
     * @var bool
     */
    protected $recursive;

    /**
     * @param string $pattern
     */
    public function __construct($pattern, $read = false, $write = false, $execute = false, $recursive = false)
    {
        $this->pattern = trim($pattern);
        $this->read = (bool) $read;
        $this->write = (bool) $write;
        $this->execute = (bool) $execute;
        $this->recursive = (bool) $recursive;
    }

    /**
     * @param string $pattern
     * @param array $modes
     *
     * @return Permission
     */
    public static function create($pattern, array $modes)
    {
        $read = false;
        $write = false;
        $execute = false;
        $recursive = false;

        $permission = new self($pattern);

        foreach ($modes as $mode) {
            switch ($mode) {
                case 'r':
                    $read = true;
                    break;
                case 'w':
                    $write = true;
                    break;
                case 'x':
                    $execute = true;
                    break;
                case 'R':
                    $recursive = true;
                    break;
            }
        }

        return new self($pattern, $read, $write, $execute, $recursive);
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return bool
     */
    public function canRead()
    {
        return $this->read;
    }

    /**
     * @param bool $read
     */
    public function setRead($read)
    {
        $this->read = (bool) $read;
    }

    /**
     * @return bool
     */
    public function canWrite()
    {
        return $this->write;
    }

    /**
     * @param bool $write
     */
    public function setWrite($write)
    {
        $this->write = (bool) $write;
    }

    /**
     * @return bool
     */
    public function canExecute()
    {
        return $this->execute;
    }

    /**
     * @param bool $execute
     */
    public function setExecute($execute)
    {
        $this->execute = (bool) $execute;
    }

    /**
     * @return bool
     */
    public function isRecursive()
    {
        return $this->recursive;
    }

    /**
     * @param bool $recursive
     */
    public function setRecursive($recursive)
    {
        $this->recursive = (bool) $recursive;
    }

    /**
     * Whether the path matches the pattern.
     *
     * @param string $path
     *
     * @return bool
     */
    public function matches($path)
    {
        return preg_match(Glob::toRegex($this->pattern), $path) === 1;
    }

    /**
     * @return string
     */
    public function getModeString()
    {
        $string = '';

        if ($this->canRead()) {
            $string .= 'r';
        }

        if ($this->canWrite()) {
            $string .= 'w';
        }

        if ($this->canExecute()) {
            $string .= 'x';
        }

        if ($this->isRecursive()) {
            $string .= 'R';
        }

        return $string;
    }
}
