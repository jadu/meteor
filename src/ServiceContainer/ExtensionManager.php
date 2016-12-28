<?php

namespace Meteor\ServiceContainer;

use Meteor\ServiceContainer\Exception\ExtensionInitializationException;

class ExtensionManager
{
    /**
     * @var ExtensionInterface[]
     */
    private $extensions = [];

    /**
     * @param ExtensionInterface[] $extensions
     */
    public function __construct(array $extensions)
    {
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
    }

    /**
     * @param ExtensionInterface $extension
     */
    public function addExtension(ExtensionInterface $extension)
    {
        $this->extensions[$extension->getConfigKey()] = $extension;
    }

    /**
     * @param string $locator
     * @param string $workingDir
     */
    public function activateExtension($locator, $workingDir)
    {
        $extension = $this->instaniateExtension($locator, $workingDir);

        if (!$extension instanceof ExtensionInterface) {
            throw new ExtensionInitializationException(sprintf(
                'Extension class `%s` does not implement ExtensionInterface.',
                get_class($extension)
            ));
        }

        $this->addExtension($extension);
    }

    /**
     * @param string $locator
     * @param string $workingDir
     */
    private function instaniateExtension($locator, $workingDir)
    {
        if (strpos($locator, '/') === 0) {
            // Absolute path
            $extensionFile = $locator;
        } else {
            // Relative paths should be relatvie from the working directory
            $extensionFile = $workingDir . '/' . $locator;
        }

        if (file_exists($extensionFile)) {
            return require $extensionFile;
        }

        $className = $locator;
        if (class_exists($className)) {
            return new $className();
        }

        throw new ExtensionInitializationException(sprintf(
            'Extension file or class `%s` could not be found.',
            $locator
        ));
    }

    /**
     * @param string $key
     *
     * @return ExtensionInterface
     */
    public function getExtension($key)
    {
        return isset($this->extensions[$key]) ? $this->extensions[$key] : null;
    }

    /**
     * @return ExtensionInterface[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @return array
     */
    public function getExtensionClasses()
    {
        return array_map('get_class', array_values($this->extensions));
    }

    public function initializeExtensions()
    {
        foreach ($this->extensions as $extension) {
            $extension->initialize($this);
        }
    }
}
