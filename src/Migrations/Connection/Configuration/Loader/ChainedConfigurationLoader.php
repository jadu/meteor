<?php

namespace Meteor\Migrations\Connection\Configuration\Loader;

class ChainedConfigurationLoader implements ConfigurationLoaderInterface
{
    /**
     * @var array
     */
    private $loaders;

    /**
     * @param array $loaders
     */
    public function __construct(array $loaders)
    {
        $this->loaders = $loaders;
    }

    /**
     * {@inheritdoc}
     */
    public function load($installDir, array $configuration = array())
    {
        foreach ($this->loaders as $loader) {
            $configuration = array_merge(array_filter($loader->load($installDir, $configuration)), $configuration);
        }

        return $configuration;
    }
}
