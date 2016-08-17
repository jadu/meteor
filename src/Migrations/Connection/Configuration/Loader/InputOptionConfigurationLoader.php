<?php

namespace Meteor\Migrations\Connection\Configuration\Loader;

use Meteor\IO\IOInterface;

class InputOptionConfigurationLoader implements ConfigurationLoaderInterface
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
    public function load($installDir, array $configuration = array())
    {
        if ($this->io->hasOption('db-name')) {
            $configuration['dbname'] = $this->io->getOption('db-name');
        }

        if ($this->io->hasOption('db-user')) {
            $configuration['user'] = $this->io->getOption('db-user');
        }

        if ($this->io->hasOption('db-password')) {
            $configuration['password'] = $this->io->getOption('db-password');
        }

        if ($this->io->hasOption('db-host')) {
            $configuration['host'] = $this->io->getOption('db-host');
        }

        if ($this->io->hasOption('db-driver')) {
            $configuration['driver'] = $this->io->getOption('db-driver');
        }

        return $configuration;
    }
}
