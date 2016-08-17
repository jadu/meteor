<?php

namespace Meteor\Migrations\Cli\Command;

use Meteor\Patch\Cli\Command\AbstractPatchCommand;

abstract class AbstractMigrationCommand extends AbstractPatchCommand
{
    /**
     * @param array $config
     *
     * @return array
     */
    protected function getMigrationConfigs(array $config)
    {
        $migrationConfigs = array();

        if (isset($config['combined'])) {
            foreach ($config['combined'] as $combinedConfig) {
                if (isset($combinedConfig['migrations'])) {
                    $migrationConfigs[$combinedConfig['name']] = $combinedConfig['migrations'];
                }
            }
        }

        if (isset($config['migrations'])) {
            $migrationConfigs[$config['name']] = $config['migrations'];
        }

        return $migrationConfigs;
    }
}
