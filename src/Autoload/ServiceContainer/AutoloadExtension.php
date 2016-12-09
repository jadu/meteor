<?php

namespace Meteor\Autoload\ServiceContainer;

use Meteor\Cli\Application;
use Meteor\Package\PackageConstants;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use RuntimeException;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AutoloadExtension extends ExtensionBase implements ExtensionInterface
{
    const SERVICE_CLASS_LOADER = 'autoload.class_loader';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'autoload';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->normalizeKeys(false)
            ->children()
                ->arrayNode('composer')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('psr-4')
                    ->prototype('array')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($value) {
                                return [$value];
                            })
                        ->end()
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $workingDir = $container->getParameter(Application::PARAMETER_WORKING_DIR);
        $classLoader = $container->get(self::SERVICE_CLASS_LOADER);

        if (isset($config['composer'])) {
            foreach ($config['composer'] as $packageName) {
                $packagePath = $workingDir.'/'.PackageConstants::PATCH_DIR.'/vendor/'.$packageName;
                $composerJsonPath = $packagePath.'/composer.json';
                if (!file_exists($composerJsonPath)) {
                    throw new RuntimeException(sprintf('Unable to find "%s" to fetch autoload paths', $composerJsonPath));
                }

                // Parse composer.json to find autoload paths
                $json = json_decode(file_get_contents($composerJsonPath), true);
                if (isset($json['autoload'])) {
                    if (isset($json['autoload']['psr-0'])) {
                        foreach ($json['autoload']['psr-0'] as $prefix => $paths) {
                            $classLoader->add($prefix, $this->normalizePaths($paths, $packagePath));
                        }
                    }

                    if (isset($json['autoload']['psr-4'])) {
                        foreach ($json['autoload']['psr-4'] as $prefix => $paths) {
                            $classLoader->addPsr4($prefix, $this->normalizePaths($paths, $packagePath));
                        }
                    }

                    if (isset($json['autoload']['classmap'])) {
                        $classLoader->addClassMap($this->normalizePaths($json['autoload']['classmap'], $packagePath));
                    }
                }
            }
        }

        if (isset($config['psr-4'])) {
            foreach ($config['psr-4'] as $namespace => $paths) {
                $classLoader->addPsr4($namespace, $this->normalizePaths($paths, $workingDir));
            }
        }
    }

    /**
     * @param array|string $paths
     * @param string $workingDir
     *
     * @return array
     */
    private function normalizePaths($paths, $rootPath)
    {
        $rootPath = rtrim($rootPath, '/').'/';

        if (!is_array($paths)) {
            $paths = [$paths];
        }

        $normalizedPaths = [];
        foreach ($paths as $path) {
            $normalizedPaths[] = $rootPath.ltrim($path, '/');
        }

        return $normalizedPaths;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
