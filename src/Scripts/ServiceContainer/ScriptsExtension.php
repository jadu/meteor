<?php

namespace Meteor\Scripts\ServiceContainer;

use Meteor\Cli\Application;
use Meteor\Cli\ServiceContainer\CliExtension;
use Meteor\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Meteor\IO\ServiceContainer\IOExtension;
use Meteor\Process\ServiceContainer\ProcessExtension;
use Meteor\Scripts\Cli\Command\RunCommand;
use Meteor\Scripts\EventListener\ScriptEventListener;
use Meteor\Scripts\Exception\CircularScriptReferenceException;
use Meteor\Scripts\Exception\ScriptReferenceException;
use Meteor\Scripts\ScriptEventProviderInterface;
use Meteor\Scripts\ScriptRunner;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
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
    private $eventNames = [];

    /**
     * @var array
     */
    private $scripts = [];

    /**
     * @var array
     */
    private $referenced = [];

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
        $extensionConfig = [];

        if (isset($config['combined'])) {
            $extensionConfigKey = $this->getConfigKey();
            foreach ($config['combined'] as $combinedConfig) {
                if (isset($combinedConfig[$extensionConfigKey])) {
                    $extensionConfig[] = $combinedConfig[$extensionConfigKey];
                }
            }
        }

        // Put the current script as last executed batch
        // Otherwise combined scripts take precedence
        $extensionConfig[] = parent::configParse($config);

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
                    return !$that->validateScripts($scripts);
                })
                ->thenInvalid('Invalid scripts')
            ->end()
            ->prototype('array')
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($value) {
                        return [$value];
                    })
                ->end()
                ->prototype('scalar')->end()
            ->end()
        ->end();
    }

    /**
     * Validates the scripts exist and do not have circular references.
     *
     * @param array $scripts
     *
     * @return bool True when valid and False when invalidd
     */
    public function validateScripts(array $scripts)
    {
        $this->scripts = $scripts;
        // NB: Resetting so loading multiple script configs does not cause circular references
        $this->referenced = [];

        foreach (array_keys($scripts) as $name) {
            // NB: Exceptions will be caught and used as the error message
            $this->checkCircularReference($name);
        }

        return true;
    }

    /**
     * Check for circular references.
     *
     * @param string $name
     *
     * @param array $seen
     *
     * @throws CircularScriptReferenceException
     * @throws ScriptReferenceException
     */
    private function checkCircularReference($name, $seen = [])
    {
        $seen[] = $name;
        foreach ($this->scripts[$name] as $command) {
            if (strpos($command, '@') === 0) {
                $command = substr($command, 1);
                if (!isset($this->scripts[$command])) {
                    throw new ScriptReferenceException(sprintf('Unable to find referenced script "%s"', $command));
                }

                if (in_array($command, $seen, true)) {
                    throw new CircularScriptReferenceException(sprintf('Circular reference detected in "%s" to "%s"', $name, $command));
                }

                $this->checkCircularReference($command, $seen);
            }
        }
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
        $container->setDefinition(self::SERVICE_EVENT_LISTENER, new Definition(ScriptEventListener::class, [
            new Reference(self::SERVICE_SCRIPT_RUNNER),
        ]))
        ->setPublic(true);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadScriptRunner(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_SCRIPT_RUNNER, new Definition(ScriptRunner::class, [
                new Reference(ProcessExtension::SERVICE_PROCESS_RUNNER),
                new Reference(IOExtension::SERVICE_IO),
                '%' . self::PARAMETER_SCRIPTS . '%',
            ])
        )
        ->setPublic(true);
    }

    private function loadRunCommand(ContainerBuilder $container)
    {
        $definition = new Definition(RunCommand::class, [
            null,
            '%' . Application::PARAMETER_CONFIG . '%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(self::SERVICE_SCRIPT_RUNNER),
        ]);

        $definition->setPublic(true);

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
            $definition->addMethodCall('addListener', [$eventName, [new Reference(self::SERVICE_EVENT_LISTENER), 'handleEvent']]);
        }
    }
}
