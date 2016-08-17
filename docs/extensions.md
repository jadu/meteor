# Extensions

Extensions provide a clean and easy way to extend Meteor functionality at runtime.

Meteor uses the [Symfony Dependency Injection component](http://symfony.com/doc/current/components/dependency_injection/introduction.html) to standardize the way objects are constructed in the application. An extension can
declare it's own services or extend services with the dependency injection container. All core functionality within Meteor are written like
extensions to ensure third-party extensions are given first-class treatmeant.

## Creating a basic extension

The `DemoExtension` class must implement `Meteor\ServiceContainer\ExtensionInterface`.

```php
<?php

namespace Jadu\MeteorDemo\ServiceContainer;

use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DemoExtension implements ExtensionInterface
{
    public function getConfigKey()
    {
        return 'demo';
    }

    public function configure(ArrayNodeDefinition $builder)
    {
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function load(ContainerBuilder $container, array $config)
    {
    }

    public function process(ContainerBuilder $container)
    {
    }
}
```

### Configuration

The [Symfony Config component](http://symfony.com/doc/current/components/config/introduction.html) is used to load and validate the `meteor.json` config file.

The `getConfigKey` method returns the config key for this extension to parse from the config.

The `configure` method is where the extension can define the config structure for the `demo` section (as used in this example).

```
public function getConfigKey()
{
    return 'demo';
}

public function configure(ArrayNodeDefinition $builder)
{
    $builder
        ->children()
            ->scalarNode('name')->end()
        ->end()
    ->end();
}
```

The above example means that the `meteor.json` config expects a section named `demo` with a `name` node.

```
{
    "demo": {
        "name": "Tom Graham"
    }
}
```

For more detailed examples and information about the Config component please see the [Symfony docs](http://symfony.com/doc/current/components/config/definition.html) on this subject.

### Loading services

The `load` method is where your extensions service should be defined.

```php
public function load(ContainerBuilder $container, array $config)
{
    $definition = new Definition('Jadu\MeteorDemo\TestService');
    $container->setDefinition(self::SERVICE_TEST, $definition);
}
```

The config passed to this method will be the processed config for this extension. So continueing the example above, this would be:

```
array(
    'name' => 'Tom Graham'
)
```

For more details examples and information about the `ContainerBuilder` class please see the [Symfony docs](http://symfony.com/doc/current/components/dependency_injection/introduction.html#basic-usage) on this subject.

Note: It is reccomended to use class constants for service names and parameter names to avoid duplication of hard-coded strings.

## Extension points

Meteor provides some standard extension points into the application:
* CLI commands
* Event subscribers
* Patch strategies
* Patch task handlers

### CLI commands

Meteor uses the [Symfony Console component](http://symfony.com/doc/current/components/console/introduction.html) for the CLI.

Commands within Meteor should ideally extend the `Meteor\Cli\Command\AbstractCommand` class, however it is not a requirement.

A simple example of a command class:

```php
<?php

namespace Jadu\MeteorDemo\Cli\Command;

use Meteor\IO\IOInterface;
use Meteor\Patch\Cli\Command\AbstractCommand;
use Meteor\Platform\PlatformInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MyFirstCommand extends AbstractCommand
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Do something
    }
}
```

To add a new command to Meteor a service definition must be tagged with `cli.command`.
Within your extensions `load` method create a service defintion as shown below:

```php
$definition = new Definition('Jadu\MeteorDemo\Cli\Command\MyFirstCommand', array(
    'demo:my-first-command',
    '%'.Application::PARAMETER_CONFIG.'%',
    new Reference(IOExtension::SERVICE_IO),
    new Reference(PlatformExtension::SERVICE_PLATFORM),
));

$definition->addTag(CliExtension::TAG_COMMAND);
$container->setDefinition(self::SERVICE_MY_FIRST_COMMAND, $definition);
```

### Event subscribers

```php
$definition = new Definition('Jadu\MeteorDemo\EventListener\MyEventListener');
$definition->addTag(EventDispatcherExtension::TAG_EVENT_SUBSCRIBER);
$container->setDefinition(self::SERVICE_MY_EVENT_LISTENER, $definition);
```

### Patch strategies

Only load services for the strategy if the strategy is selected in the config. This can be achieved by checking the strategy parameter as shown in the example below:

```php
public function load(ContainerBuilder $container, array $config)
{
    if ($container->getParameter(PatchExtension::PARAMETER_STRATEGY) !== self::STRATEGY_NAME) {
        return;
    }

    $definition = new Definition('Jadu\MeteorDemo\Patch\Strategy\DemoPatchStrategy');
    $container->setDefinition(PatchExtension::SERVICE_STRATEGY_PREFIX.'.'.self::STRATEGY_NAME, $definition);
}
```

Ensure your strategy service name is in the format `patch.strategy.[strategy name]`. This should be done within your extension using the class constants provided. For example:

```php
PatchExtension::SERVICE_STRATEGY_PREFIX.'.'.self::STRATEGY_NAME
```

Meteor will set the active strategy using the service name and expects it to be in this format. If this pattern isn't followed then Meteor will not be able to find the custom strategy.

### Patch task handlers

```php
$definition = new Definition('Jadu\MeteorDemo\Patch\Task\MyTaskHandler');
$definition->addTag(PatchExtension::TAG_TASK_HANDLER, array(
    'task' => 'Jadu\MeteorDemo\Patch\Task\MyTask',
));
$container->setDefinition(self::SERVICE_MY_TASK_HANDLER, $definition);
```
