<?php

namespace Meteor\Migrations\Connection\Configuration\Loader;

use Meteor\IO\IOInterface;

class InputQuestionConfigurationLoader implements ConfigurationLoaderInterface
{
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
     * {@inheritdoc}
     */
    public function load($installDir, array $configuration = [])
    {
        if (!isset($configuration['dbname']) || empty($configuration['dbname'])) {
            $configuration['dbname'] = $this->io->ask('Enter the database name for migrations');
        }

        if (!isset($configuration['user']) || empty($configuration['user'])) {
            $configuration['user'] = $this->io->ask('Enter the database username for migrations');
        }

        if (!isset($configuration['password']) || empty($configuration['password'])) {
            $configuration['password'] = $this->io->askAndHideAnswer('Enter the database password for migrations');
        }

        if (!isset($configuration['host']) || empty($configuration['host'])) {
            $configuration['host'] = $this->io->ask('Enter the database host for migrations', 'localhost');
        }

        if (!isset($configuration['driver']) || empty($configuration['driver'])) {
            $configuration['driver'] = $this->io->ask('Enter the database driver for migrations', 'pdo_mysql');
        }

        return $configuration;
    }
}
