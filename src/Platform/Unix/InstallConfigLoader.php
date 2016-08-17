<?php

namespace Meteor\Platform\Unix;

use InvalidArgumentException;

class InstallConfigLoader
{
    const CONFIG_NAME = 'install.conf';

    /**
     * @param string $path
     *
     * @return JaduInstallConfig
     */
    public function load($path)
    {
        $configPath = $path.'/'.self::CONFIG_NAME;
        if (!file_exists($configPath)) {
            throw new InvalidArgumentException(sprintf('Unable to open install.conf file "%s"', $configPath));
        }

        $values = @parse_ini_file($configPath);
        if ($values === false) {
            throw new InvalidArgumentException(sprintf('Unable to parse install.conf file "%s"', $configPath));
        }

        return new InstallConfig($values);
    }
}
