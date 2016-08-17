<?php

namespace Meteor\Patch\Task;

use Composer\Semver\Semver;
use Meteor\IO\IOInterface;

class CheckModuleCmsDependencyHandler
{
    const MODULE_CMS_DEPENDENCY_FILE = 'MODULE_CMS_DEPENDENCY';

    const CMS_VERSION_FILE = 'VERSION';

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param IOInterface $io
     */
    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }

    /**
     * @param CheckModuleCmsDependency $task
     *
     * @return bool
     */
    public function handle(CheckModuleCmsDependency $task)
    {
        $cmsModuleDependencyFile = $task->workingDir.'/'.self::MODULE_CMS_DEPENDENCY_FILE;
        if (!file_exists($cmsModuleDependencyFile)) {
            // There is no CMS module dependency to check
            return true;
        }

        $workingDirCmsVersionFile = $task->workingDir.'/'.self::CMS_VERSION_FILE;
        $installDirCmsVersionFile = $task->installDir.'/'.self::CMS_VERSION_FILE;
        if (!file_exists($installDirCmsVersionFile) && !file_exists($workingDirCmsVersionFile)) {
            // The CMS version file could not be found
            return true;
        }

        $cmsVersionRequirement = trim(file_get_contents($cmsModuleDependencyFile));

        // NB: For backwards compatibility the default operator is `>=`, however `==` can still be used to specify the versions must be equal
        if (preg_match('/^\d+\.\d+\.\d+$/', $cmsVersionRequirement)) {
            $cmsVersionRequirement = '>='.$cmsVersionRequirement;
        }

        if (file_exists($workingDirCmsVersionFile)) {
            // If the install dir doesn't satisfy the requirements, check the working dir
            // this can happen when we combine packages and the dependency is not satisfied by installation, but by the combined package

            if (!$this->checkVersionRequirement($cmsVersionRequirement, $workingDirCmsVersionFile)) {
                $cmsVersion = trim(file_get_contents($workingDirCmsVersionFile));
                $this->io->error(sprintf('The combined package CMS version %s does not meet the version requirement %s', $cmsVersion, $cmsVersionRequirement));

                return false;
            }
        } elseif (!$this->checkVersionRequirement($cmsVersionRequirement, $installDirCmsVersionFile)) {
            $cmsVersion = trim(file_get_contents($installDirCmsVersionFile));
            $this->io->error(sprintf('The installed CMS version %s does not meet the version requirement %s', $cmsVersion, $cmsVersionRequirement));

            return false;
        }

        return true;
    }

    /**
     * @param string $versionRequirement
     * @param string $path
     *
     * @return bool
     */
    protected function checkVersionRequirement($versionRequirement, $path)
    {
        if (file_exists($path)) {
            $cmsVersion = trim(file_get_contents($path));

            return Semver::satisfies($cmsVersion, $versionRequirement);
        }

        return false;
    }
}
