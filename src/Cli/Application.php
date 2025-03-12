<?php

namespace Meteor\Cli;

use Composer\Autoload\ClassLoader;
use Exception;
use Meteor\Autoload\ServiceContainer\AutoloadExtension;
use Meteor\Cli\ServiceContainer\CliExtension;
use Meteor\Configuration\ConfigurationLoader;
use Meteor\Configuration\Exception\ConfigurationLoadingException;
use Meteor\Configuration\ServiceContainer\ConfigurationExtension;
use Meteor\IO\ServiceContainer\IOExtension;
use Meteor\Logger\ServiceContainer\LoggerExtension;
use Meteor\ServiceContainer\ContainerLoader;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Application extends BaseApplication
{
    public const PARAMETER_CONFIG = 'config';
    public const PARAMETER_WORKING_DIR = 'working_dir';

    /**
     * @var ConfigurationLoader
     */
    private $configurationLoader;

    /**
     * @var ExtensionManager
     */
    private $extensionManager;

    /**
     * @var ClassLoader
     */
    private $classLoader;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private static $logo = "
  __  __ ______ _______ ___________  _____
 |  \/  |  ____|__   __|______  __ \|  __ \
 | \  / | |__     | |   _____/ |  | | |__) |
 | |\/| |  __|    | |  |_____  |  | |  _  /
 | |  | | |____   | |   _____\ |__| | | \ \
 |_|  |_|______|  |_|  |___________/|_|  \_\

    ";

    /**
     * @param string $name
     * @param string $version
     * @param ConfigurationLoader $configurationLoader
     * @param ExtensionManager $extensionManager
     * @param ClassLoader $classLoader
     */
    public function __construct($name, $version, ConfigurationLoader $configurationLoader, ExtensionManager $extensionManager, ClassLoader $classLoader)
    {
        $this->configurationLoader = $configurationLoader;
        $this->extensionManager = $extensionManager;
        $this->classLoader = $classLoader;

        parent::__construct($name, $version);
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return self::$logo . parent::getHelp();
    }

    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();

        $definition->addOption(new InputOption('--working-dir', '-d', InputOption::VALUE_REQUIRED, 'If specified, use the given directory as working directory'));
        $definition->addOption(new InputOption('--patch-path', null, InputOption::VALUE_REQUIRED, '<fg=yellow>[DEPRECATED] Use the --working-dir/-d option instead</>'));

        return $definition;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $workingDir = rtrim($input->getParameterOption(['--working-dir', '-d']), '/');
        if ($workingDir === '') {
            // Fallback to the old --patch-path option
            $workingDir = rtrim($input->getParameterOption(['--patch-path']), '/');
            if ($workingDir === '') {
                // Lastly fallback to the current directory
                $workingDir = getcwd();
            }
        }

        $workingDir = realpath($workingDir);

        // Set the config path
        $configPath = $this->configurationLoader->resolve($workingDir);

        if (!$configPath) {
            throw new ConfigurationLoadingException(sprintf('The config file could not be found in the working dir `%s`', $workingDir));
        }

        $config = $this->configurationLoader->parse($configPath);

        // Create the container and add commands registered by extensions
        $this->container = $this->createContainer($input, $output, $config, $workingDir);

        foreach ($this->container->getParameter(CliExtension::PARAMETER_COMMAND_SERVICE_IDS) as $commandId) {
            $this->add($this->container->get($commandId));
        }

        return parent::doRun($input, $output);
    }

    /**
     * @param Exception $exception
     * @param OutputInterface $output
     */
    public function renderException(Exception $exception, OutputInterface $output)
    {
        parent::renderException($exception, $output);

        if ($this->container === null) {
            return;
        }

        $io = $this->container->get(IOExtension::SERVICE_IO);

        $io->note('If you are unsure or unable to resolve these issues, please contact Jadu support.');

        $logger = $this->container->get(LoggerExtension::SERVICE_LOGGER);
        if (!$logger->isEnabled()) {
            return;
        }

        do {
            $messages = ['', 'Error: ' . get_class($exception), $exception->getMessage(), '', 'Exception trace:'];

            $trace = $exception->getTrace();
            array_unshift($trace, [
                'function' => '',
                'file' => $exception->getFile() !== null ? $exception->getFile() : 'n/a',
                'line' => $exception->getLine() !== null ? $exception->getLine() : 'n/a',
                'args' => [],
            ]);

            for ($i = 0, $count = count($trace); $i < $count; ++$i) {
                $class = $trace[$i]['class'] ?? '';
                $type = $trace[$i]['type'] ?? '';
                $function = $trace[$i]['function'];
                $file = $trace[$i]['file'] ?? 'n/a';
                $line = $trace[$i]['line'] ?? 'n/a';

                $messages[] = sprintf(' %s%s%s() at %s:%s', $class, $type, $function, $file, $line);
            }
        } while ($exception = $exception->getPrevious());

        $logger->log($messages);

        // Disable the logger to prevent logging the message about the log
        $logger->disable();

        $io->note(sprintf('Please provide the log files located at %s when raising this issue.', $logger->getPath()));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $config
     * @param string $workingDir
     *
     * @return ContainerInterface
     */
    private function createContainer(InputInterface $input, OutputInterface $output, array $config, $workingDir)
    {
        $container = new ContainerBuilder();
        $container->set(AutoloadExtension::SERVICE_CLASS_LOADER, $this->classLoader);
        $container->set(CliExtension::SERVICE_INPUT, $input);
        $container->set(CliExtension::SERVICE_OUTPUT, $output);
        $container->set(ConfigurationExtension::SERVICE_LOADER, $this->configurationLoader);
        $container->setParameter(self::PARAMETER_WORKING_DIR, $workingDir);

        $extension = new ContainerLoader($this->configurationLoader, $this->extensionManager);
        $config = $extension->load($container, $config, $workingDir);
        $container->setParameter(self::PARAMETER_CONFIG, $config);

        $container->compile();

        return $container;
    }
}
