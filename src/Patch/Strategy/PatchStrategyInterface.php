<?php

namespace Meteor\Patch\Strategy;

use Symfony\Component\Console\Input\InputDefinition;

interface PatchStrategyInterface
{
    /**
     * @param string $patchDir
     * @param string $installDir
     * @param array $options
     *
     * @return array
     */
    public function apply($patchDir, $installDir, array $options);

    /**
     * @param InputDefinition $definition
     */
    public function configureApplyCommand(InputDefinition $definition);

    /**
     * @param string $backupDir
     * @param string $patchDir
     * @param string $installDir
     * @param array $options
     *
     * @return array
     */
    public function rollback($backupDir, $patchDir, $installDir, array $intermediateBackupDirs, array $options);

    /**
     * @param InputDefinition $definition
     */
    public function configureRollbackCommand(InputDefinition $definition);
}
