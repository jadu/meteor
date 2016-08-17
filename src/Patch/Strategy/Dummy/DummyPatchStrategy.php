<?php

namespace Meteor\Patch\Strategy\Dummy;

use Meteor\Patch\Strategy\PatchStrategyInterface;
use Symfony\Component\Console\Input\InputDefinition;

/**
 * @codeCoverageIgnore
 */
class DummyPatchStrategy implements PatchStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply($patchDir, $installDir, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureApplyCommand(InputDefinition $definition)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function rollback($patchDir, $backupDir, $installDir, array $intermediateBackupDirs, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureRollbackCommand(InputDefinition $definition)
    {
    }
}
