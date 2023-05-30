<?php

namespace Meteor\Patch\Task;

use Meteor\IO\IOInterface;
use Meteor\Patch\Version\VersionComparer;

class DisplayVersionInfoHandler
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
     * @param DisplayVersionInfo $task
     * @param array $config
     */
    public function handle(DisplayVersionInfo $task, array $config)
    {
        $versions = $this->versionComparer->comparePackage($task->workingDir, $task->installDir, $config);

        foreach ($versions as $version) {
            if (strpos($version->getNewVersion(), 'dev-') === 0 || strpos($version->getCurrentVersion(), 'dev-') === 0) {
                $status = '<fg=green>Development</>';
            } elseif ($version->isGreaterThan()) {
                $status = '<fg=green>Newer</>';
            } elseif ($version->isLessThan()) {
                $status = '<fg=red>Older</>';
            } else {
                $status = '<fg=yellow>No change</>';
            }

            $rows[] = [
                $version->getPackageName(),
                $version->getFileName(),
                $version->getCurrentVersion(),
                $version->getNewVersion(),
                $status,
            ];
        }

        if (isset($rows)) {
            $this->io->table([
                'Name',
                'Version file',
                'Current version',
                'Patch version',
                'Status',
            ], $rows);
        }
    }
}
