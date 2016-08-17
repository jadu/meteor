<?php

namespace Meteor\Configuration;

class ConfigurationWriter
{
    /**
     * @param string $path
     * @param array $config
     */
    public function write($path, array $config)
    {
        // Filter empty/null values from the config
        $config = array_filter($config, function ($value) {
            return $value !== null && (!is_array($value) || !empty($value));
        });

        $json = json_encode($config);
        if ($json === false) {
            throw new RuntimeException('Unable to encode config to JSON');
        }

        $json = JsonFormatter::format($json, true, true);

        file_put_contents($path, $json);
    }
}
