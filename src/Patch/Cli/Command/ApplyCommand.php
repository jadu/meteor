<?php

namespace Meteor\Patch\Cli\Command;

use Composer\Semver\Semver;
use InvalidArgumentException;
use Meteor\IO\IOInterface;
use Meteor\Logger\LoggerInterface;
use Meteor\Patch\Event\PatchEvents;
use Meteor\Patch\Exception\PhpVersionException;
use Meteor\Patch\Lock\Locker;
use Meteor\Patch\Strategy\PatchStrategyInterface;
use Meteor\Patch\Task\TaskBusInterface;
use Meteor\Platform\PlatformInterface;
use Meteor\Scripts\ScriptRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ApplyCommand extends AbstractPatchCommand
{
    /**
     * @var TaskBusInterface
     */
    private $taskBus;

    /**
     * @var PatchStrategyInterface
     */
    private $strategy;

    /**
     * @var Locker
     */
    private $locker;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ScriptRunner
     */
    private $scriptRunner;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $phpVersion;

    /**
     * @param string $name
     * @param array $config
     * @param IOInterface $io
     * @param PlatformInterface $platform
     * @param TaskBusInterface $taskBus
     * @param PatchStrategyInterface $strategy
     * @param Locker $locker
     * @param EventDispatcherInterface $eventDispatcher
     * @param ScriptRunner $scriptRunner
     * @param LoggerInterface $logger
     * @param string $phpVersion
     */
    public function __construct(
        $name,
        array $config,
        IOInterface $io,
        PlatformInterface $platform,
        TaskBusInterface $taskBus,
        PatchStrategyInterface $strategy,
        Locker $locker,
        EventDispatcherInterface $eventDispatcher,
        ScriptRunner $scriptRunner,
        LoggerInterface $logger,
        $phpVersion = PHP_VERSION
    ) {
        $this->taskBus = $taskBus;
        $this->strategy = $strategy;
        $this->locker = $locker;
        $this->eventDispatcher = $eventDispatcher;
        $this->scriptRunner = $scriptRunner;
        $this->logger = $logger;
        $this->setPhpVersion($phpVersion);

        parent::__construct($name, $config, $io, $platform);
    }

    protected function configure()
    {
        $this->setName('patch:apply');
        $this->setDescription('Applies a patch');

        $this->addOption('skip-lock', null, InputOption::VALUE_NONE, 'Skip any existing lock files to force a patch');
        $this->addOption('skip-scripts', null, InputOption::VALUE_NONE, 'Skip script execution');
        $this->addOption('ignore-unavailable-migrations', null, InputOption::VALUE_NONE, 'Ignore unavailable migrations.');

        $this->strategy->configureApplyCommand($this->getDefinition());

        parent::configure();
    }

    /**
     * Override the current PHP version that's in use.
     *
     * @param string $version
     */
    public function setPhpVersion($version)
    {
        // Check if the current version has metadata in it
        // example : 5.6.0-1ubuntu3.25
        if (count(explode('-', $version)) > 1) {
            $versionWithMetadata = explode('-', $version);
            $version = $versionWithMetadata[0];
        }

        $this->phpVersion = $version;
    }

    /**
     * Returns the current set php version
     * @return string|void
     */
    public function getPhpVersion()
    {
        return $this->phpVersion;
    }

    /**
     * Compiles a complete list of PHP version constraints from customer and combined packages
     * and verifies if the current PHP version is compatiable.
     *
     * @param array $config
     *
     * @throws PhpVersionException
     */
    protected function checkPhpConstraint(array $config)
    {
        $versions = [];

        if (isset($config['package']['php'])) {
            $versions[$config['name']] = $config['package']['php'];
        }

        if (isset($config['combined'])) {
            foreach ($config['combined'] as $combinedPackage => $combinedConfig) {
                if (isset($combinedConfig['package']['php'])) {
                    $versions[$combinedConfig['name']] = $combinedConfig['package']['php'];
                }
            }
        }

        foreach ($versions as $package => $version) {
            if (!Semver::satisfies($this->phpVersion, $version)) {
                throw new PhpVersionException(sprintf('Your PHP version (%s) is not sufficient enough for the package "%s", which requires %s', $this->phpVersion, $package, $version));
            }
        }

        return;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workingDir = $this->getWorkingDir();
        $installDir = $this->getInstallDir();

        if ($workingDir === $installDir) {
            throw new InvalidArgumentException('The working directory cannot be the same as the install directory');
        }

        $config = $this->getConfiguration();

        // NB: Should be checked prior to locking for patching
        $this->checkPhpConstraint($config);

        $this->platform->setInstallDir($installDir);
        $this->scriptRunner->setWorkingDir($installDir);
        $this->logger->enable($this->getLogPath($workingDir));

        $this->io->title(sprintf('Applying the <info>%s</> patch', $config['name']));

        if (!$this->io->getOption('skip-lock')) {
            $this->locker->lock($installDir);
        }

        if (!$this->io->getOption('skip-scripts')) {
            $this->eventDispatcher->dispatch(PatchEvents::PRE_APPLY, new Event());
        }

        $tasks = $this->strategy->apply($workingDir, $installDir, $this->io->getOptions());
        foreach ($tasks as $task) {
            $result = $this->taskBus->run($task, $this->getConfiguration());
            if ($result === false) {
                return 1;
            }
        }

        if (!$this->io->getOption('skip-scripts')) {
            $this->eventDispatcher->dispatch(PatchEvents::POST_APPLY, new Event());
        }

        if (!$this->io->getOption('skip-lock')) {
            $this->locker->unlock($installDir);
        }

        $this->io->success('Patch complete');
    }
}
