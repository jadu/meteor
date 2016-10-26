<?php

namespace Meteor\Configuration;

use Meteor\Configuration\Exception\ConfigurationLoadingException;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationLoader
{
    const CONFIG_NAME = 'meteor.json';
    const DIST_CONFIG_NAME = 'meteor.json.dist';
    const PACKAGE_CONFIG_NAME = 'meteor.json.package';

    /**
     * @var ExtensionManager
     */
    private $extensionManager;

    /**
     * @var NodeInterface
     */
    private $tree;

    /**
     * @var TreeBuilder
     */
    private $treeBuilder;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @param ExtensionManager $extensionManager
     * @param TreeBuilder $treeBuilder
     * @param Processor $processor
     */
    public function __construct(ExtensionManager $extensionManager, TreeBuilder $treeBuilder = null, Processor $processor = null)
    {
        $this->extensionManager = $extensionManager;
        $this->treeBuilder = $treeBuilder ?: new TreeBuilder();
        $this->processor = $processor ?: new Processor();
    }

    /**
     * @param array $extensions
     */
    public function buildTree(array $extensions)
    {
        $rootNode = $this->treeBuilder->root('meteor');
        $childrenNode = $rootNode->children();

        $childrenNode->scalarNode('name')->defaultValue(uniqid('package'))->end();
        $childrenNode->arrayNode('extensions')->prototype('scalar')->end();

        foreach ($extensions as $extension) {
            $extension->configure($childrenNode->arrayNode($extension->getConfigKey()));
        }

        $combinedNode = $childrenNode->arrayNode('combined');
        $combinedChildrenNode = $combinedNode->prototype('array')->children();

        $combinedChildrenNode->scalarNode('name')->defaultValue(uniqid('package'))->end();
        $combinedChildrenNode->arrayNode('extensions')->prototype('scalar')->end();

        // Build the combined section to allow all extension configs
        foreach ($extensions as $extension) {
            $extension->configure($combinedChildrenNode->arrayNode($extension->getConfigKey()));
        }

        $combinedChildrenNode->end();
        $combinedNode->end();

        $childrenNode->end();

        $this->tree = $this->treeBuilder->buildTree();
    }

    /**
     * @param string $path
     *
     * @return array
     */
    public function parse($path)
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new ConfigurationLoadingException(sprintf('Configuration file `%s` not found.', $path));
        }

        $config = json_decode(file_get_contents($path), true);
        if ($config === null) {
            throw new ConfigurationLoadingException(sprintf('Unable to parse JSON in configuration file `%s`.', $path));
        }

        return (array) $config;
    }

    /**
     * @param array $config
     *
     * @return array
     */
    public function process(array $config)
    {
        if ($this->tree === null) {
            throw new ConfigurationLoadingException('The configuration cannot be processed as the tree was not built');
        }

        return $this->processor->process($this->tree, [$config]);
    }

    /**
     * @param string $path
     * @param bool $strict
     *
     * @throws ConfigurationLoadingException
     *
     * @return array
     */
    public function load($path, $strict = true)
    {
        $configPath = $this->resolve($path);
        if (!$configPath) {
            throw new ConfigurationLoadingException(sprintf('The config file could not be found in `%s`', $path));
        }

        $config = $this->parse($configPath);

        if (!$strict) {
            $allowedKeys = array_keys($this->extensionManager->getExtensions());
            $allowedKeys[] = 'name';
            $allowedKeys[] = 'combined';

            // Only process the allowed keys to prevent unrecognised option exceptions
            $config = array_intersect_key($config, array_flip($allowedKeys));
        }

        return $this->process($config);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function resolve($path)
    {
        $paths = array_filter(
            [
                // NB: The package from packages should chosen first
                $path.'/'.self::PACKAGE_CONFIG_NAME,
                $path.'/'.self::CONFIG_NAME,
                $path.'/'.self::DIST_CONFIG_NAME,
            ],
            'is_file'
        );

        if (count($paths)) {
            return current($paths);
        }
    }
}
