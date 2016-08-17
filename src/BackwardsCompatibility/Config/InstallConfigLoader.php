<?php

namespace Jadu\Meteor\Config;

use InvalidArgumentException;

/**
 * This class is from the original version of Meteor. Some migrations use it so it needs to be
 * available to them for backwards compatibility. No new migrations should be using this class.
 *
 * @deprecated Use Meteor\Platform\Unix\InstallConfigLoader instead
 */
class InstallConfigLoader
{
    public function load($path)
    {
        $configPath = $path.'/install.conf';
        if (!file_exists($configPath)) {
            throw new InvalidArgumentException('Unable to find install.conf file');
        }

        $values = @parse_ini_file($configPath);
        if (!$values) {
            throw new InvalidArgumentException('Unable to parse install.conf file');
        }

        return new InstallConfig($values);
    }
}
