<?php

namespace Meteor\Migrations;

use Doctrine\Migrations\Exception\MigrationException;
use Meteor\IO\IOInterface;
use Meteor\Migrations\Configuration\ConfigurationFactory;
use Meteor\Migrations\Configuration\FileConfiguration;

class Migrator
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
     * @param string $type
     * @param array $config
     * @param string $patchDir
     *
     * @return bool
     */
    public function validateConfiguration(string $type, array $config, string $patchDir): bool
    {
        switch ($type) {
            case MigrationsConstants::TYPE_FILE:
                $config['directory'] = $config['directory'] . '/' . FileConfiguration::MIGRATION_DIRECTORY;
                if (is_dir($patchDir . '/' . $config['directory'])) {
                    return true;
                }

                return false;
            case MigrationsConstants::TYPE_DATABASE:
                if (is_dir($patchDir . '/' . $config['directory'])) {
                    return true;
                }

                return false;
            default:
                return false;
        }
    }

    /**
     * @param string $patchDir
     * @param string $installDir
     * @param array $config
     * @param string $type
     * @param string $version
     * @param bool $ignoreUnavailableMigrations
     *
     * @return bool
     */
    public function migrate($patchDir, $installDir, array $config, $type, $version, $ignoreUnavailableMigrations)
    {
        $configuration = $this->configurationFactory->createConfiguration($type, $config, $patchDir, $installDir);
        $executedMigrations = $configuration->getMigratedVersions();
        $availableMigrations = $configuration->getAvailableVersions();
        $executedUnavailableMigrations = array_diff($executedMigrations, $availableMigrations);

        if ($version === '0') {
            // NB: "first" and "0" are equivalent but `resolveVersionAlias` expects "first"
            $version = 'first';
        }

        $resolvedVersion = $configuration->resolveVersionAlias($version);
        if ($resolvedVersion === null) {
            switch ($version) {
                case 'prev':
                    $this->io->error('Already at first version.');
                    break;
                case 'next':
                    $this->io->error('Already at latest version.');
                    break;
                default:
                    $this->io->error(sprintf('Unknown version "%s".', $version));
            }

            return true;
        }

        $from = (string) $configuration->getCurrentVersion();
        $to = (string) $resolvedVersion;

        $migrations = $configuration->getMigrations();
        if (!isset($migrations[$to]) && $to > 0) {
            throw MigrationException::unknownMigrationVersion($to);
        }

        $direction = $from > $to ? 'down' : 'up';
        $migrationsToExecute = $configuration->getMigrationsToExecute($direction, $to);

        if (empty($migrationsToExecute)) {
            $this->io->note(sprintf('No %s migrations to execute', $type));

            return true;
        }

        if ($executedUnavailableMigrations) {
            $this->io->note(sprintf('You have %s previously executed migrations that are not registered migrations.', count($executedUnavailableMigrations)));
            foreach ($executedUnavailableMigrations as $executedUnavailableMigration) {
                $this->io->text(' * ' . $configuration->getDateTime($executedUnavailableMigration) . ' (<comment>' . $executedUnavailableMigration . '</>)');
            }

            if (!$ignoreUnavailableMigrations) {
                $confirmation = $this->io->askConfirmation('Are you sure you wish to continue?', false);
                if (!$confirmation) {
                    $this->io->error('Migrations cancelled.');

                    return false;
                }
            }
        }

        $this->io->text(sprintf('Executing %s migration <info>%s</> to <comment>%s</> from <comment>%s</>', $type, $direction, $to, $from));

        $time = 0;
        foreach ($migrationsToExecute as $version) {
            $result = $version->execute($direction);
            $time += $result->getTime();
        }

        $this->io->success([
            sprintf('%s %s migrations executed', $type, count($migrationsToExecute)),
            sprintf('Finished in %s', $time),
        ]);

        // NB: Migration version file will be updated when creating a backup rather than after running migrations

        return true;
    }

    /**
     * @param string $patchDir
     * @param string $installDir
     * @param array $config
     * @param string $type
     * @param string $version
     * @param string $direction
     *
     * @return bool
     */
    public function execute($patchDir, $installDir, array $config, $type, $version, $direction)
    {
        $this->io->text(sprintf('Executing %s migration <comment>%s</> <info>%s</>', $type, $version, $direction));

        $configuration = $this->configurationFactory->createConfiguration($type, $config, $patchDir, $installDir);
        $version = $configuration->getVersion($version);
        $version->execute($direction);

        return true;
    }
}
