<?php

namespace Meteor;

use Meteor\Cli\Application;
use Meteor\Cli\ServiceContainer\CliExtension;
use Meteor\Configuration\ConfigurationLoader;
use Meteor\Configuration\ServiceContainer\ConfigurationExtension;
use Meteor\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Meteor\Filesystem\ServiceContainer\FilesystemExtension;
use Meteor\IO\ServiceContainer\IOExtension;
use Meteor\Logger\ServiceContainer\LoggerExtension;
use Meteor\Migrations\ServiceContainer\MigrationsExtension;
use Meteor\Package\ServiceContainer\PackageExtension;
use Meteor\Patch\ServiceContainer\PatchExtension;
use Meteor\Patch\Strategy\Overwrite\ServiceContainer\OverwritePatchStrategyExtension;
use Meteor\Permissions\ServiceContainer\PermissionsExtension;
use Meteor\Platform\ServiceContainer\PlatformExtension;
use Meteor\Process\ServiceContainer\ProcessExtension;
use Meteor\Scripts\ServiceContainer\ScriptsExtension;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;

class ApplicationFactory
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'meteor';
    }

    /**
     * @return string
     */
    protected function getVersion()
    {
        return '@git-version@';
    }

    /**
     * @return ExtensionInterface[]
     */
    protected function getDefaultExtensions()
    {
        return array(
            new CliExtension(),
            new ConfigurationExtension(),
            new EventDispatcherExtension(),
            new FilesystemExtension(),
            new IOExtension(),
            new LoggerExtension(),
            new MigrationsExtension(),
            new PackageExtension(),
            new PatchExtension(),
            new OverwritePatchStrategyExtension(),
            new PermissionsExtension(),
            new PlatformExtension(),
            new ProcessExtension(),
            new ScriptsExtension(),
        );
    }

    /**
     * @return Application
     */
    public function createApplication()
    {
        $extensionManager = $this->createExtensionManager();
        $configurationLoader = $this->createConfigurationLoader($extensionManager);

        return new Application($this->getName(), $this->getVersion(), $configurationLoader, $extensionManager);
    }

    /**
     * @param ExtensionManager $extensionManager
     *
     * @return ConfigurationLoader
     */
    protected function createConfigurationLoader(ExtensionManager $extensionManager)
    {
        return new ConfigurationLoader($extensionManager);
    }

    /**
     * @return ExtensionManager
     */
    protected function createExtensionManager()
    {
        return new ExtensionManager($this->getDefaultExtensions());
    }
}
