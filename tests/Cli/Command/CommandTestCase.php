<?php

namespace Meteor\Cli\Command;

use Meteor\Cli\Application;
use Meteor\Configuration\ConfigurationLoader;
use Meteor\ServiceContainer\ExtensionManager;

abstract class CommandTestCase extends \PHPUnit_Framework_TestCase
{
    protected $command;
    protected $application;
    protected $tester;

    public function setUp()
    {
        $extensionManager = new ExtensionManager([]);
        $configurationLoader = new ConfigurationLoader($extensionManager);

        $this->command = $this->createCommand();
        $this->application = new Application('meteor', '2.0.0', $configurationLoader, $extensionManager);
        $this->application->add($this->command);

        $this->tester = new CommandTester($this->command);
    }

    abstract public function createCommand();
}
