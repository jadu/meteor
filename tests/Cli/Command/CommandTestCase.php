<?php

namespace Meteor\Cli\Command;

use Composer\Autoload\ClassLoader;
use Meteor\Cli\Application;
use Meteor\Configuration\ConfigurationLoader;
use Meteor\ServiceContainer\ExtensionManager;
use PHPUnit\Framework\TestCase;

abstract class CommandTestCase extends TestCase
{
    protected $command;
    protected $application;
    protected $tester;

    protected function setUp(): void
    {
        $extensionManager = new ExtensionManager([]);
        $configurationLoader = new ConfigurationLoader($extensionManager);
        $classLoader = new ClassLoader();

        $this->command = $this->createCommand();
        $this->application = new Application('meteor', '2.0.0', $configurationLoader, $extensionManager, $classLoader);
        $this->application->add($this->command);

        $this->tester = new CommandTester($this->command);
    }

    abstract public function createCommand();
}
