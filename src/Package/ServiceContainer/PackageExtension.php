<?php

namespace Meteor\Package\ServiceContainer;

use InvalidArgumentException;
use Meteor\Cli\Application;
use Meteor\Cli\ServiceContainer\CliExtension;
use Meteor\Configuration\ServiceContainer\ConfigurationExtension;
use Meteor\Filesystem\ServiceContainer\FilesystemExtension;
use Meteor\IO\ServiceContainer\IOExtension;
use Meteor\ServiceContainer\ExtensionBase;
use Meteor\ServiceContainer\ExtensionInterface;
use Meteor\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class PackageExtension extends ExtensionBase implements ExtensionInterface
{
    const PARAMETER_PROVIDER = 'package.provider';
    const SERVICE_COMBINED_PACKAGE_DEPENDENCY_CHECKER = 'package.combined.package_dependency_checker';
    const SERVICE_COMBINED_PACKAGE_COMBINER = 'package.combined.package_combiner';
    const SERVICE_COMBINED_PACKAGE_RESOLVER = 'package.combined.package_resolver';
    const SERVICE_COMMAND_PACKAGE = 'package.cli.command.package';
    const SERVICE_COMPOSER_DEPENDENCY_CHECKER = 'package.composer.dependency_checker';
    const SERVICE_MIGRATIONS_COPIER = 'package.migrations.copier';
    const SERVICE_PACKAGE_ARCHIVER = 'package.archiver';
    const SERVICE_PACKAGE_CREATOR = 'package.creator';
    const SERVICE_PACKAGE_EXTRACTOR = 'package.extractor';
    const SERVICE_PACKAGE_NAME_RESOLVER = 'package.name_resolver';
    const SERVICE_PROVIDER_PREFIX = 'package.provider';
    const SERVICE_PROVIDER = 'package.provider';

    private $extensions;

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'package';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
        $this->extensions = $extensionManager->getExtensions();
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('version')
                ->end()
                ->arrayNode('files')
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('provider')
                ->end()
                ->arrayNode('combine')
                    ->normalizeKeys(false)
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('composer')
                    ->normalizeKeys(false)
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('php')
                ->end()
            ->end()
        ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $container->setParameter(self::PARAMETER_PROVIDER, isset($config['provider']) ? $config['provider'] : null);

        $this->loadCombinedPackageDependencyChecker($container);
        $this->loadCombinedPackageCombiner($container);
        $this->loadCombinedPackageResolver($container);
        $this->loadComposerDependencyChecker($container);
        $this->loadMigrationsCopier($container);
        $this->loadPackageArchiver($container);
        $this->loadPackageExtractor($container);
        $this->loadPackageCreator($container);
        $this->loadPackageCommand($container);
        $this->loadPackageNameResolver($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadCombinedPackageDependencyChecker(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_COMBINED_PACKAGE_DEPENDENCY_CHECKER, new Definition('Meteor\Package\Combined\CombinedPackageDependencyChecker'));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadCombinedPackageCombiner(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_COMBINED_PACKAGE_COMBINER, new Definition('Meteor\Package\Combined\PackageCombiner', [
            new Reference(ConfigurationExtension::SERVICE_LOADER),
            new Reference(FilesystemExtension::SERVICE_FILESYSTEM),
            new Reference(self::SERVICE_PACKAGE_EXTRACTOR),
            new Reference(self::SERVICE_MIGRATIONS_COPIER),
            new Reference(IOExtension::SERVICE_IO),
        ]));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadCombinedPackageResolver(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_COMBINED_PACKAGE_RESOLVER, new Definition('Meteor\Package\Combined\CombinedPackageResolver', [
            new Reference(self::SERVICE_COMBINED_PACKAGE_COMBINER),
            new Reference(self::SERVICE_COMBINED_PACKAGE_DEPENDENCY_CHECKER),
            new Reference(FilesystemExtension::SERVICE_FILESYSTEM),
            new Reference(IOExtension::SERVICE_IO),
            new Reference(self::SERVICE_PROVIDER, ContainerInterface::NULL_ON_INVALID_REFERENCE),
        ]));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadComposerDependencyChecker(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_COMPOSER_DEPENDENCY_CHECKER, new Definition('Meteor\Package\Composer\ComposerDependencyChecker'));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadMigrationsCopier(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_MIGRATIONS_COPIER, new Definition('Meteor\Package\Migrations\MigrationsCopier', [
            new Reference(FilesystemExtension::SERVICE_FILESYSTEM),
            new Reference(IOExtension::SERVICE_IO),
        ]));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadPackageArchiver(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_PACKAGE_ARCHIVER, new Definition('Meteor\Package\PackageArchiver', [
            new Reference(FilesystemExtension::SERVICE_FILESYSTEM),
            new Reference(IOExtension::SERVICE_IO),
        ]));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadPackageExtractor(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_PACKAGE_EXTRACTOR, new Definition('Meteor\Package\PackageExtractor'));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadPackageCreator(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_PACKAGE_CREATOR, new Definition('Meteor\Package\PackageCreator', [
            new Reference(FilesystemExtension::SERVICE_FILESYSTEM),
            new Reference(self::SERVICE_PACKAGE_ARCHIVER),
            new Reference(self::SERVICE_PACKAGE_NAME_RESOLVER),
            new Reference(self::SERVICE_MIGRATIONS_COPIER),
            new Reference(self::SERVICE_COMBINED_PACKAGE_RESOLVER),
            new Reference(self::SERVICE_COMPOSER_DEPENDENCY_CHECKER),
            new Reference(ConfigurationExtension::SERVICE_WRITER),
            new Reference(IOExtension::SERVICE_IO),
        ]));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadPackageCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Meteor\Package\Cli\Command\PackageCommand', [
            null,
            '%' . Application::PARAMETER_CONFIG . '%',
            new Reference(IOExtension::SERVICE_IO),
            new Reference(self::SERVICE_PACKAGE_CREATOR),
        ]);
        $definition->addTag(CliExtension::TAG_COMMAND);
        $container->setDefinition(self::SERVICE_COMMAND_PACKAGE, $definition);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadPackageNameResolver(ContainerBuilder $container)
    {
        $container->setDefinition(self::SERVICE_PACKAGE_NAME_RESOLVER, new Definition('Meteor\Package\PackageNameResolver'));
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $providerName = $container->getParameter(self::PARAMETER_PROVIDER);
        if (!$providerName) {
            return;
        }

        $providerServiceId = self::SERVICE_PROVIDER_PREFIX . '.' . $providerName;
        if (!$container->has($providerServiceId)) {
            throw new InvalidArgumentException(sprintf('Unable to find package provider "%s".', $providerName));
        }

        $container->setAlias(self::SERVICE_PROVIDER, $providerServiceId);
    }
}
