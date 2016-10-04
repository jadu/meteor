<?php

namespace Meteor\ServiceContainer;

use Meteor\Configuration\ConfigurationLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContainerLoader
{
    const PARAMETER_CONFIG = 'config';

    /**
     * @var ConfigurationLoader
     */
    private $configurationLoader;

    /**
     * @var ExtensionManager
     */
    private $extensionManager;

    /**
     * @param ConfigurationLoader $configurationLoader
     * @param ExtensionManager $extensionManager
     */
    public function __construct(ConfigurationLoader $configurationLoader, ExtensionManager $extensionManager)
    {
        $this->configurationLoader = $configurationLoader;
        $this->extensionManager = $extensionManager;
    }

    /**
     * @param ContainterBuilder $container
     * @param array $config
     * @param string $workingDir
     *
     * @return array
     */
    public function load(ContainerBuilder $container, array $config, $workingDir)
    {
        $config = $this->initializeExtensions($container, $config, $workingDir);
        $config = $this->processConfig($config);
        $this->loadExtensions($container, $config);

        return $config;
    }

    /**
     * @param array $config
     *
     * @return array
     */
    private function processConfig(array $config)
    {
        $this->configurationLoader->buildTree($this->extensionManager->getExtensions());

        return $this->configurationLoader->process($config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     * @param string $workingDir
     *
     * @return array
     */
    private function initializeExtensions(ContainerBuilder $container, array $config, $workingDir)
    {
        if (isset($config['extensions'])) {
            foreach ($config['extensions'] as $extensionLocator) {
                $this->extensionManager->activateExtension($extensionLocator, $workingDir);
            }
        }

        $this->extensionManager->initializeExtensions();

        $container->setParameter('extensions', $this->extensionManager->getExtensionClasses());

        return $config;
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function loadExtensions(ContainerBuilder $container, array $config)
    {
        foreach ($this->extensionManager->getExtensions() as $extension) {
            $this->loadExtension(
                $container,
                $extension,
                $extension->configParse($config)
            );
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param ExtensionInterface $extension
     * @param array $config
     */
    private function loadExtension(ContainerBuilder $container, ExtensionInterface $extension, array $config)
    {
        $extension->load($container, $config);
        $container->addCompilerPass($extension);
    }
}
