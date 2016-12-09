<?php

namespace Meteor\Migrations\Outputter;

use InvalidArgumentException;
use Meteor\IO\IOInterface;
use Meteor\Migrations\Configuration\AbstractConfiguration;
use Meteor\Migrations\Configuration\ConfigurationFactory;
use Meteor\Migrations\MigrationsConstants;

class StatusOutputter
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
     * @param bool $showVersions
     */
    public function output($patchDir, $installDir, array $config, $type, $showVersions)
    {
        $configuration = $this->createConfiguration($type, $config, $patchDir, $installDir);

        $formattedVersions = [];
        foreach (['prev', 'current', 'next', 'latest'] as $alias) {
            $version = $configuration->resolveVersionAlias($alias);
            if ($version === null) {
                if ($alias === 'next') {
                    $formattedVersions[$alias] = 'Already at latest version';
                } elseif ($alias === 'prev') {
                    $formattedVersions[$alias] = 'Already at first version';
                }
            } elseif ($version === '0') {
                $formattedVersions[$alias] = '<comment>0</comment>';
            } else {
                $formattedVersions[$alias] = $configuration->formatVersion($version).' (<comment>'.$version.'</comment>)';
            }
        }

        $executedMigrations = $configuration->getMigratedVersions();
        $availableMigrations = $configuration->getAvailableVersions();
        $executedUnavailableMigrations = array_diff($executedMigrations, $availableMigrations);
        $numExecutedUnavailableMigrations = count($executedUnavailableMigrations);
        $newMigrations = count(array_diff($availableMigrations, $executedMigrations));

        $rows = [
            ['Version table name', $configuration->getMigrationsTableName()],
            ['Migrations namespace', $configuration->getMigrationsNamespace()],
            ['Migrations directory', $configuration->getMigrationsDirectory()],
            ['Previous version', $formattedVersions['prev']],
            ['Current version', $formattedVersions['current']],
            ['Next version', $formattedVersions['next']],
            ['Latest version', $formattedVersions['latest']],
            ['Executed migrations', count($executedMigrations)],
            ['Executed unavailable migrations', $numExecutedUnavailableMigrations > 0 ? '<error>'.$numExecutedUnavailableMigrations.'</error>' : 0],
            ['Available migrations', count($availableMigrations)],
            ['New migrations', $newMigrations > 0 ? '<question>'.$newMigrations.'</question>' : 0],
        ];

        $this->io->table([], $rows);

        if ($showVersions) {
            $migrations = $configuration->getMigrations();
            if (!empty($migrations)) {
                $this->io->section('Available migrations:');
                foreach ($migrations as $migration) {
                    $status = in_array($migration->getVersion(), $executedMigrations, true) ? '<fg=green>Migrated</>' : '<fg=red>Not migrated</>';

                    $this->io->text(sprintf(
                        ' * %s (<comment>%s</comment>) %s',
                        $configuration->formatVersion($migration->getVersion()),
                        $migration->getVersion(),
                        $status
                    ));
                }

                $this->io->newLine();
            }

            if (!empty($executedUnavailableMigrations)) {
                $this->io->section('Previously executed unavailable migrations:');
                foreach ($executedUnavailableMigrations as $version) {
                    $this->io->text(sprintf(
                        ' * %s (<comment>%s</comment>)',
                        $configuration->formatVersion($version),
                        $version
                    ));
                }

                $this->io->newLine();
            }
        }
    }

    /**
     * @param string $type
     * @param array $config
     * @param string $patchDir
     * @param string $installDir
     *
     * @return AbstractConfiguration
     */
    private function createConfiguration($type, $config, $patchDir, $installDir)
    {
        if ($type === MigrationsConstants::TYPE_FILE) {
            return $this->configurationFactory->createFileConfiguration($config, $patchDir, $installDir);
        }

        if ($type === MigrationsConstants::TYPE_DATABASE) {
            return $this->configurationFactory->createDatabaseConfiguration($config, $patchDir, $installDir);
        }

        throw new InvalidArgumentException('Invalid migration type');
    }
}
