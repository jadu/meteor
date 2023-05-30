<?php

namespace Meteor\Patch\Task;

use Meteor\IO\IOInterface;
use Meteor\Patch\Version\VersionComparer;

class CheckVersionHandler
{
    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var VersionComparer
     */
    private $versionComparer;

    /**
     * @param IOInterface $io
     * @param VersionComparer $versionComparer
     */
    public function __construct(IOInterface $io, VersionComparer $versionComparer)
    {
        $this->io = $io;
        $this->versionComparer = $versionComparer;
    }

    /**
     * @param CheckVersion $task
     * @param array $config
     *
     * @return bool
     */
    public function handle(CheckVersion $task, array $config)
    {
        $versions = $this->versionComparer->comparePackage($task->workingDir, $task->installDir, $config);

        foreach ($versions as $version) {
            // if we have a development package we do not want to error on version checks
            if (strpos($version->getNewVersion(), 'dev-') === 0 || strpos($version->getCurrentVersion(), 'dev-') === 0) {
                continue;
            }

            if ($task->operator === CheckVersion::GREATER_THAN_OR_EQUAL && $version->isLessThan()) {
                $this->io->error('All versions within the patch must be greater than or equal to the current version');

                return false;
            }

            if ($task->operator === CheckVersion::LESS_THAN_OR_EQUAL && $version->isGreaterThan()) {
                $this->io->error('All versions within the patch must be less than or equal to the current version');

                return false;
            }
        }

        if ($this->io->isInteractive()) {
            $confirmation = $this->io->askConfirmation('Are you sure you wish to continue?', true);
            if (!$confirmation) {
                $this->io->error('Patch cancelled.');

                return false;
            }
        }

        return true;
    }
}
