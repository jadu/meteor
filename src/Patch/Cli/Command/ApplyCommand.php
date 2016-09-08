<?php

namespace Meteor\Patch\Cli\Command;

use InvalidArgumentException;
use Meteor\IO\IOInterface;
use Meteor\Logger\LoggerInterface;
use Meteor\Patch\Event\PatchEvents;
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
        LoggerInterface $logger
    ) {
        $this->taskBus = $taskBus;
        $this->strategy = $strategy;
        $this->locker = $locker;
        $this->eventDispatcher = $eventDispatcher;
        $this->scriptRunner = $scriptRunner;
        $this->logger = $logger;

        parent::__construct($name, $config, $io, $platform);
    }

    protected function configure()
    {
        $this->setName('patch:apply');
        $this->setDescription('Applies a patch');

        $this->addOption('skip-lock', null, InputOption::VALUE_NONE, 'Skip any existing lock files to force a patch');
        $this->strategy->configureApplyCommand($this->getDefinition());

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workingDir = $this->getWorkingDir();
        $installDir = $this->getInstallDir();

        if ($workingDir === $installDir) {
            throw new InvalidArgumentException('The working directory cannot be the same as the install directory');
        }

        $config = $this->getConfiguration();

        $this->platform->setInstallDir($installDir);
        $this->scriptRunner->setWorkingDir($installDir);
        $this->logger->enable($this->getLogPath($workingDir));

        $this->io->title(sprintf('Applying the <info>%s</> patch', $config['name']));

        if (!$this->io->getOption('skip-lock')) {
            $this->locker->lock($installDir);
        }

        $this->eventDispatcher->dispatch(PatchEvents::PRE_APPLY, new Event());

        $tasks = $this->strategy->apply($workingDir, $installDir, $this->io->getOptions());
        foreach ($tasks as $task) {
            $result = $this->taskBus->run($task, $this->getConfiguration());
            if ($result === false) {
                return 1;
            }
        }

        $this->eventDispatcher->dispatch(PatchEvents::POST_APPLY, new Event());

        if (!$this->io->getOption('skip-lock')) {
            $this->locker->unlock($installDir);
        }

        $this->io->success('Patch complete');
    }
}
