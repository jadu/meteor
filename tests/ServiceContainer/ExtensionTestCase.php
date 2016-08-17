<?php

namespace Meteor\ServiceContainer;

use Meteor\Cli\Application;
use Meteor\Cli\ServiceContainer\CliExtension;
use Meteor\Configuration\ConfigurationLoader;
use Meteor\Configuration\ServiceContainer\ConfigurationExtension;
use Meteor\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Meteor\Filesystem\ServiceContainer\FilesystemExtension;
use Meteor\IO\ServiceContainer\IOExtension;
use Meteor\Logger\ServiceContainer\LoggerExtension;
use Meteor\Migrations\ServiceContainer\MigrationsExtension;
use Meteor\Package\Provider\Dummy\ServiceContainer\DummyPackageProviderExtension;
use Meteor\Package\Provider\GoogleDrive\ServiceContainer\GoogleDrivePackageProviderExtension;
use Meteor\Package\ServiceContainer\PackageExtension;
use Meteor\Patch\ServiceContainer\PatchExtension;
use Meteor\Patch\Strategy\Dummy\ServiceContainer\DummyPatchStrategyExtension;
use Meteor\Patch\Strategy\Overwrite\ServiceContainer\OverwritePatchStrategyExtension;
use Meteor\Permissions\ServiceContainer\PermissionsExtension;
use Meteor\Platform\ServiceContainer\PlatformExtension;
use Meteor\Process\ServiceContainer\ProcessExtension;
use Meteor\Scripts\ServiceContainer\ScriptsExtension;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class ExtensionTestCase extends \PHPUnit_Framework_TestCase
{
    protected $configurationLoader;
    protected $extensionManager;
    protected $containerLoader;
    protected $input;
    protected $output;

    public function setUp()
    {
        $this->extensionManager = new ExtensionManager($this->createExtensions());
        $this->configurationLoader = new ConfigurationLoader($this->extensionManager);

        $this->containerLoader = new ContainerLoader(
            $this->configurationLoader,
            $this->extensionManager
        );

        $this->input = new ArrayInput(array());
        $this->output = new NullOutput();
    }

    protected function loadContainer(array $config)
    {
        $container = new ContainerBuilder();

        $container->set(CliExtension::SERVICE_INPUT, $this->input);
        $container->set(CliExtension::SERVICE_OUTPUT, $this->output);
        $container->set(ConfigurationExtension::SERVICE_LOADER, $this->configurationLoader);

        $config = $this->containerLoader->load($container, $config, null);
        $container->setParameter(Application::PARAMETER_CONFIG, $config);

        $container->compile();

        return $container;
    }

    protected function processConfiguration(array $config)
    {
        $this->configurationLoader->buildTree($this->createExtensions());

        return $this->configurationLoader->process($config);
    }

    public function createExtensions()
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
            new DummyPackageProviderExtension(),
            new GoogleDrivePackageProviderExtension(),
            new PatchExtension(),
            new DummyPatchStrategyExtension(),
            new OverwritePatchStrategyExtension(),
            new PermissionsExtension(),
            new PlatformExtension(),
            new ProcessExtension(),
            new ScriptsExtension(),
        );
    }
}
