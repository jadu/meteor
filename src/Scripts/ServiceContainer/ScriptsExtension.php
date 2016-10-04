<?php

namespace Meteor\Scripts\ServiceContainer;

use Meteor\Cli\Application;
use Meteor\Cli\ServiceContainer\CliExtension;
use Meteor\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Meteor\IO\ServiceContainer\IOExtension;
use Meteor\Process\ServiceContainer\ProcessExtension;
use Meteor\Scripts\ScriptEventProviderInterface;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionManager;
use Meteor\ServiceContainer\ExtensionInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ScriptsExtension extends ExtensionBase implements ExtensionInterface
{
    const PARAMETER_SCRIPTS = 'scripts';
    const SERVICE_COMMAND_RUN = 'scripts.cli.command.run';
    const SERVICE_EVENT_LISTENER = 'scripts.event_listener';
    const SERVICE_SCRIPT_RUNNER = 'scripts.script_runner';

    /**
     * @var array
     */
    private $eventNames = array();

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'scripts';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
        $extensions = $extensionManager->getExtensions();
        foreach ($extensions as $extension) {
            if ($extension instanceof ScriptEventProviderInterface) {
                $this->eventNames = array_merge($this->eventNames, $extension->getEventNames());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configParse(array $config)
    {
        $extensionConfig = array();
        $extensionConfig[] = parent::configParse($config);

        if (isset($config['combined'])) {
            $extensionConfigKey = $this->getConfigKey();
            foreach ($config['combined'] as $combinedConfig) {
                if (isset($combinedConfig[$extensionConfigKey])) {
                    $extensionConfig[] = $combinedConfig[$extensionConfigKey];
                }
            }
        }

        return $extensionConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $that = $this;

        $builder
            ->normalizeKeys(false)
            ->validate()
                ->ifTrue(function ($scripts) use ($that) {
                    return $that->hasInfiniteRecursion($scripts);
                })
                ->thenInvalid('Infinite recursion detected in scripts')
            ->end()
            ->prototype('array')
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($value) {
                        return array($value);
                    })
                ->end()
                ->prototype('scalar')->end()
            ->end()
        ->end();
    }

    public function hasInfiniteRecursion(array $scripts)
    {
        foreach ($scripts as $name => $commands) {
            foreach ($commands as $command) {
                if (strpos($command, '@') === 0) {
                    $command = substr($command, 1);
                    if ($command === $name) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $container->setParameter(self::PARAMETER_SCRIPTS, $config);

        $this->loadEventListener($container);
        $this->loadScriptRunner($container);
        $this->loadRunCommand($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadEventListener(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_EVENT_LISTENER, new Definition('Meteor\Scripts\EventListener\ScriptEventListener', array(
            new Reference(self::SERVICE_SCRIPT_RUNNER),
        )));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadScriptRunner(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_SCRIPT_RUNNER, new Definition('Meteor\Scripts\ScriptRunner', array(
            new Reference(ProcessExtension::SERVICE_PROCESS_RUNNER),
            new Reference(IOExtension::SERVICE_IO),
            '%'.self::PARAMETER_SCRIPTS.'%',
        )));
    }

    private function loadRunCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Scripts\Cli\Command\RunCommand', array(
            null,
            '%'.Application::PARAMETER_CONFIG.'%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(self::SERVICE_SCRIPT_RUNNER),
        ));

        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_RUN, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(EventDispatcherExtension::SERVICE_EVENT_DISPATCHER);

        foreach ($this->eventNames as $eventName) {
            $definition->addMethodCall('addListener', array($eventName, array(new Reference(self::SERVICE_EVENT_LISTENER), 'handleEvent')));
        }
    }
}
