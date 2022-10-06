<?php

namespace Meteor\Migrations\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class MigrationsExtensionTest extends ExtensionTestCase
{
    public function testServicesCanBeInstantiated()
    {
        $container = $this->loadContainer([]);

        foreach ($this->getServiceIds() as $serviceId) {
            static::assertTrue($container->has($serviceId), sprintf('Container has "%s" service', $serviceId));
        }
    }

    private function getServiceIds()
    {
        return [
            MigrationsExtension::SERVICE_CONFIGURATION_FACTORY,
            MigrationsExtension::SERVICE_CONNECTION_CONFIGURATION_LOADER,
            MigrationsExtension::SERVICE_CONNECTION_CONFIGURATION_LOADER_INPUT_OPTION,
            MigrationsExtension::SERVICE_CONNECTION_CONFIGURATION_LOADER_INPUT_QUESTION,
            MigrationsExtension::SERVICE_CONNECTION_CONFIGURATION_LOADER_SYSTEM,
            MigrationsExtension::SERVICE_CONNECTION_FACTORY,
            MigrationsExtension::SERVICE_COMMAND_EXECUTE_DATABASE_MIGRATION,
            MigrationsExtension::SERVICE_COMMAND_EXECUTE_FILE_MIGRATION,
            MigrationsExtension::SERVICE_COMMAND_GENERATE_DATABASE_MIGRATION,
            MigrationsExtension::SERVICE_COMMAND_GENERATE_FILE_MIGRATION,
            MigrationsExtension::SERVICE_COMMAND_MIGRATE_DATABASE,
            MigrationsExtension::SERVICE_COMMAND_MIGRATE_FILES,
            MigrationsExtension::SERVICE_COMMAND_DATABASE_MIGRATION_STATUS,
            MigrationsExtension::SERVICE_COMMAND_FILE_MIGRATION_STATUS,
            MigrationsExtension::SERVICE_COMMAND_DATABASE_MIGRATION_VERSION,
            MigrationsExtension::SERVICE_COMMAND_FILE_MIGRATION_VERSION,
            MigrationsExtension::SERVICE_MIGRATION_GENERATOR,
            MigrationsExtension::SERVICE_MIGRATOR,
            MigrationsExtension::SERVICE_STATUS_OUTPUTTER,
            MigrationsExtension::SERVICE_VERSION_FILE_MANAGER,
            MigrationsExtension::SERVICE_VERSION_FILE_MIGRATION_VERSION_STORAGE_FACTORY,
        ];
    }

    public function testCanOmitMigrationsConfiguration()
    {
        $config = $this->processConfiguration([]);

        $this->assertArrayNotHasKey('migrations', $config);
    }
}
