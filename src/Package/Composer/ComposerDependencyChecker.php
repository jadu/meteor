<?php

namespace Meteor\Package\Composer;

use Composer\Semver\Semver;
use Meteor\Package\Composer\Exception\ComposerDependenciesException;

class ComposerDependencyChecker
{
    /**
     * Check whether Composer is being used.
     *
     * @param string $workingDir
     *
     * @return ComposerRequirement[]
     */
    public function getRequirements($workingDir)
    {
        $jsonPath = $workingDir . '/composer.json';

        if (!file_exists($jsonPath)) {
            return [];
        }

        $json = json_decode(file_get_contents($jsonPath), true);
        if ($json === null) {
            throw ComposerDependenciesException::forInvalidJsonFile($jsonPath);
        }

        $requirements = [];
        if (isset($json['require'])) {
            foreach ($json['require'] as $packageName => $versionConstraint) {
                if ($packageName === 'php') {
                    $requirements[] = new ComposerPhpVersion($versionConstraint);
                } elseif (preg_match('/.*\/.*/', $packageName)) {
                    $requirements[] = new ComposerRequirement($packageName, $versionConstraint);
                }
            }
        }

        return $requirements;
    }

    /**
     * @param string $workingDir
     * @param array $config
     *
     * @return bool
     *
     * @throws ComposerDependenciesException
     */
    public function check($workingDir, array $config)
    {
        $requirements = [];
        if (isset($config['combined'])) {
            foreach ($config['combined'] as $combinedConfig) {
                if (isset($combinedConfig['package']) && isset($combinedConfig['package']['composer'])) {
                    foreach ($combinedConfig['package']['composer'] as $packageName => $versionConstraint) {
                        $requirements[] = new ComposerRequirement($packageName, $versionConstraint);
                    }
                }
            }
        }

        if (empty($requirements)) {
            return true;
        }

        $lockPath = $workingDir . '/composer.lock';
        if (!file_exists($lockPath)) {
            throw ComposerDependenciesException::forMissingLockFile($lockPath);
        }

        $lock = json_decode(file_get_contents($lockPath), true);
        if ($lock === null) {
            throw ComposerDependenciesException::forInvalidLockFile($lockPath);
        }

        $packages = [];
        if (isset($lock['packages'])) {
            foreach ($lock['packages'] as $package) {
                $packages[strtolower($package['name'])] = $package['version'];
            }
        }

        $problems = [];
        foreach ($requirements as $requirement) {
            if (!isset($packages[strtolower($requirement->getPackageName())])) {
                $problems[] = new ComposerProblem($requirement, ComposerProblem::REASON_MISSING);
            } elseif (!Semver::satisfies($packages[strtolower($requirement->getPackageName())], $requirement->getVersionConstraint())) {
                $problems[] = new ComposerProblem($requirement, ComposerProblem::REASON_CONSTRAINT, $packages[strtolower($requirement->getPackageName())]);
            }
        }

        if (!empty($problems)) {
            throw ComposerDependenciesException::withProblems($lockPath, $problems);
        }

        return true;
    }

    /**
     * @param ComposerRequirement[] $requirements
     * @param array $config
     *
     * @return array
     */
    public function addRequirements(array $requirements, array $config)
    {
        if (!isset($config['package'])) {
            $config['package'] = [];
        }

        $config['package']['composer'] = [];

        foreach ($requirements as $requirement) {
            if ($requirement instanceof ComposerRequirement) {
                $config['package']['composer'][$requirement->getPackageName()] = $requirement->getVersionConstraint();
            } elseif ($requirement instanceof ComposerPhpVersion) {
                $config['package']['php'] = $requirement->getVersionConstraint();
            }
        }

        return $config;
    }
}
