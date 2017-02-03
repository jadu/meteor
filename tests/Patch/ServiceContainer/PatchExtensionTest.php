<?php

namespace Meteor\Patch\ServiceContainer;

use Meteor\ServiceContainer\ExtensionTestCase;

class PatchExtensionTest extends ExtensionTestCase
{
    public function testServicesCanBeInstantiated()
    {
        $container = $this->loadContainer([]);

        foreach ($this->getServiceIds() as $serviceId) {
            $container->get($serviceId);
        }
    }

    private function getServiceIds()
    {
        return [
            PatchExtension::SERVICE_COMMAND_APPLY,
            PatchExtension::SERVICE_COMMAND_CLEAR_LOCK,
            PatchExtension::SERVICE_COMMAND_ROLLBACK,
            PatchExtension::SERVICE_COMMAND_VERSION_INFO,
            PatchExtension::SERVICE_LOCKER,
            PatchExtension::SERVICE_STRATEGY,
            PatchExtension::SERVICE_TASK_BUS,
            PatchExtension::SERVICE_TASK_BACKUP_FILES_HANDLER,
            PatchExtension::SERVICE_TASK_CHECK_DATABASE_CONNECTION_HANDLER,
            PatchExtension::SERVICE_TASK_CHECK_DISK_SPACE_HANDLER,
            PatchExtension::SERVICE_TASK_CHECK_MODULE_CMS_DEPENDENCY_HANDLER,
            PatchExtension::SERVICE_TASK_CHECK_VERSION_HANDLER,
            PatchExtension::SERVICE_TASK_CHECK_WRITE_PERMISSION_HANDLER,
            PatchExtension::SERVICE_TASK_COPY_FILES_HANDLER,
            PatchExtension::SERVICE_TASK_DISPLAY_VERSION_INFO_HANDLER,
            PatchExtension::SERVICE_TASK_MIGRATE_DOWN_HANDLER,
            PatchExtension::SERVICE_TASK_MIGRATE_UP_HANDLER,
            PatchExtension::SERVICE_TASK_SET_PERMISSIONS_HANDLER,
            PatchExtension::SERVICE_TASK_UPDATE_MIGRATION_VERSION_FILES_HANDLER,
            PatchExtension::SERVICE_VERSION_COMPARER,
        ];
    }
}
