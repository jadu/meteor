<?php

namespace Meteor\Migrations\Connection\Configuration\Loader;

class SystemConfigurationLoader implements ConfigurationLoaderInterface
{
    const CONFIG_FILENAME = 'config/system.xml';

    /**
     * {@inheritdoc}
     */
    public function load($installDir, array $configuration = [])
    {
        $path = $installDir . '/' . self::CONFIG_FILENAME;

        if (!file_exists($path) || !is_readable($path)) {
            return $configuration;
        }

        $xml = simplexml_load_file($path);
        if (intval(trim($xml->db_use_dsn)) !== 0) {
            return $configuration;
        }

        if (isset($xml->db_name) && !empty($xml->db_name)) {
            $configuration['dbname'] = trim($xml->db_name);
        }

        if (isset($xml->db_username) && !empty($xml->db_username)) {
            $configuration['user'] = trim($xml->db_username);
        }

        if (isset($xml->db_password) && !empty($xml->db_password)) {
            $configuration['password'] = trim($xml->db_password);
        }

        if (isset($xml->db_host) && !empty($xml->db_host)) {
            $configuration['host'] = trim($xml->db_host);
        }

        if (isset($xml->db_port) && !empty($xml->db_port)) {
            $configuration['port'] = trim($xml->db_port);
        }

        if (isset($xml->db_dbms) && !empty($xml->db_dbms)) {
            if (stripos(trim($xml->db_dbms), 'mysql') !== false) {
                $configuration['driver'] = 'pdo_mysql';
            } elseif (stripos(trim($xml->db_dbms), 'mssql') !== false) {
                $configuration['driver'] = 'sqlsrv';
            }
        }

        return $configuration;
    }
}
