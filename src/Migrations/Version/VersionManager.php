<?php

namespace Meteor\Migrations\Version;

use Meteor\IO\IOInterface;
use Meteor\Migrations\Configuration\ConfigurationFactory;

class VersionManager
{
    /**
     * @var ConfigurationFactory
     */
    private $configurationFactory;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param ConfigurationFactory $configurationFactory
     * @param IOInterface $io
     */
    public function __construct(
        ConfigurationFactory $configurationFactory,
        IOInterface $io
    ) {
        $this->configurationFactory = $configurationFactory;
        $this->io = $io;
    }

    /**
     * @param string $patchDir
     * @param string $installDir
     * @param array $config
     * @param string $type
     * @param string $version
     *
     * @return bool
     */
    public function markMigrated($patchDir, $installDir, array $config, $type, $version)
    {
        $configuration = $this->configurationFactory->createConfiguration($type, $config, $patchDir, $installDir);

        if (!$configuration->hasVersion($version)) {
            $this->io->error(sprintf('Unknown migration version "%s"', $version));

            return false;
        }

        $version = $configuration->getVersion($version);
        if ($configuration->hasVersionMigrated($version)) {
            $this->io->error(sprintf('The version "%s" has already been migrated.', $version));

            return false;
        }

        $version->markMigrated();

        $this->io->text(sprintf('Marked <info>%s</> as migrated', $version));

        return true;
    }

    /**
     * @param string $patchDir
     * @param string $installDir
     * @param array $config
     * @param string $type
     * @param string $version
     *
     * @return bool
     */
    public function markNotMigrated($patchDir, $installDir, array $config, $type, $version)
    {
        $configuration = $this->configurationFactory->createConfiguration($type, $config, $patchDir, $installDir);

        if (!$configuration->hasVersion($version)) {
            $this->io->error(sprintf('Unknown migration version "%s"', $version));

            return false;
        }

        $version = $configuration->getVersion($version);
        if (!$configuration->hasVersionMigrated($version)) {
            $this->io->error(sprintf('The version "%s" has not been migrated.', $version));

            return false;
        }

        $version->markNotMigrated();

        $this->io->text(sprintf('Marked <info>%s</> as not migrated', $version));

        return true;
    }

    /**
     * @param string $patchDir
     * @param string $installDir
     * @param array $config
     * @param string $type
     *
     * @return bool
     */
    public function markAllMigrated($patchDir, $installDir, array $config, $type)
    {
        $configuration = $this->configurationFactory->createConfiguration($type, $config, $patchDir, $installDir);
        $availableVersions = $configuration->getAvailableVersions();
        foreach ($availableVersions as $version) {
            $version = $configuration->getVersion($version);
            if (!$configuration->hasVersionMigrated($version)) {
                $version->markMigrated();

                $this->io->text(sprintf('Marked <info>%s</> as migrated', $version));
            }
        }

        return true;
    }

    /**
     * @param string $patchDir
     * @param string $installDir
     * @param array $config
     * @param string $type
     *
     * @return bool
     */
    public function markAllNotMigrated($patchDir, $installDir, array $config, $type)
    {
        $configuration = $this->configurationFactory->createConfiguration($type, $config, $patchDir, $installDir);
        $availableVersions = $configuration->getAvailableVersions();
        foreach ($availableVersions as $version) {
            $version = $configuration->getVersion($version);
            if ($configuration->hasVersionMigrated($version)) {
                $version->markNotMigrated();

                $this->io->text(sprintf('Marked <info>%s</> as not migrated', $version));
            }
        }

        return true;
    }
}
