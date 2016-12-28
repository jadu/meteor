<?php

namespace Meteor\Package\Combined;

use Meteor\Package\Combined\Exception\CombinedPackageDependenciesException;
use Meteor\Package\PackageConstants;

class CombinedPackageDependencyChecker
{
    /**
     * @param string $tempDir
     * @param array $config
     *
     * @throws CombinedPackageDependenciesException
     *
     * @return bool
     */
    public function check($tempDir, array $config)
    {
        $requirements = $this->resolveRequirements($config);
        if (empty($requirements)) {
            return true;
        }

        // Normalise package names and create a keyed array of configs
        $combinedPackages = [];
        if (isset($config['combined'])) {
            foreach ($config['combined'] as $combinedConfig) {
                $combinedPackages[strtolower($combinedConfig['name'])] = $combinedConfig;
            }
        }

        $problems = [];
        foreach ($requirements as $requirement) {
            if (!isset($combinedPackages[strtolower($requirement->getPackageName())])) {
                $problems[] = new CombinedPackageProblem($requirement, CombinedPackageProblem::REASON_MISSING);
            } else {
                $combinedConfig = $combinedPackages[strtolower($requirement->getPackageName())];
                if (isset($combinedConfig['package']['version'])) {
                    $version = '0'; // Default version if the version file is not found
                    $versionFile = $tempDir . '/' . PackageConstants::PATCH_DIR . '/' . $combinedConfig['package']['version'];
                    if (file_exists($versionFile)) {
                        $version = trim(file_get_contents($versionFile));
                    }

                    if ($version !== $requirement->getVersion()) {
                        $problems[] = new CombinedPackageProblem($requirement, CombinedPackageProblem::REASON_VERSION, $version);
                    }
                }
            }
        }

        if (!empty($problems)) {
            throw CombinedPackageDependenciesException::withProblems($problems);
        }

        return true;
    }

    /**
     * @param array $config
     *
     * @return CombinedPackageRequirement[]
     */
    private function resolveRequirements(array $config)
    {
        $requirements = [];

        if (isset($config['package']) && isset($config['package']['combine'])) {
            foreach ($config['package']['combine'] as $packageName => $version) {
                $requirements[$packageName] = new CombinedPackageRequirement($packageName, $version);
            }
        }

        if (isset($config['combined'])) {
            foreach ($config['combined'] as $combinedConfig) {
                $requirements = array_merge($requirements, $this->resolveRequirements($combinedConfig));
            }
        }

        return $requirements;
    }
}
