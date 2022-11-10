<?php

namespace Meteor\Migrations\Connection\Configuration\Loader;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class SystemConfigurationLoaderTest extends TestCase
{
    private $loader;

    protected function setUp(): void
    {
        $this->loader = new SystemConfigurationLoader();
    }

    public function testLoadsFromSystemXml()
    {
        $systemXml = <<<'XML'
<?xml version="1.0" encoding="utf-8" ?>
<system xmlns:config="http://www.jadu.co.uk/schema/config">
    <db_host>localhost</db_host>
    <db_port>3306</db_port>
    <db_username>jadu</db_username>
    <db_password>password</db_password>
    <db_name>jadudb</db_name>
    <db_dbms>pdo_mysql</db_dbms>
    <db_use_dsn>0</db_use_dsn>
</system>
XML;

        vfsStream::setup('root', null, [
            'config' => [
                'system.xml' => $systemXml,
            ],
        ]);

        static::assertSame([
            'dbname' => 'jadudb',
            'user' => 'jadu',
            'password' => 'password',
            'host' => 'localhost',
            'port' => '3306',
            'driver' => 'pdo_mysql',
        ], $this->loader->load(vfsStream::url('root')));
    }

    public function testUsesPdoMysqlForMysql()
    {
        $systemXml = <<<'XML'
<?xml version="1.0" encoding="utf-8" ?>
<system xmlns:config="http://www.jadu.co.uk/schema/config">
    <db_host>localhost</db_host>
    <db_port>3306</db_port>
    <db_username>jadu</db_username>
    <db_password>password</db_password>
    <db_name>jadudb</db_name>
    <db_dbms>mysql</db_dbms>
    <db_use_dsn>0</db_use_dsn>
</system>
XML;

        vfsStream::setup('root', null, [
            'config' => [
                'system.xml' => $systemXml,
            ],
        ]);

        static::assertSame([
            'dbname' => 'jadudb',
            'user' => 'jadu',
            'password' => 'password',
            'host' => 'localhost',
            'port' => '3306',
            'driver' => 'pdo_mysql',
        ], $this->loader->load(vfsStream::url('root')));
    }

    public function testUsesSqlsrvForMssql()
    {
        $systemXml = <<<'XML'
<?xml version="1.0" encoding="utf-8" ?>
<system xmlns:config="http://www.jadu.co.uk/schema/config">
    <db_host>localhost</db_host>
    <db_port>3306</db_port>
    <db_username>jadu</db_username>
    <db_password>password</db_password>
    <db_name>jadudb</db_name>
    <db_dbms>mssql</db_dbms>
    <db_use_dsn>0</db_use_dsn>
</system>
XML;

        vfsStream::setup('root', null, [
            'config' => [
                'system.xml' => $systemXml,
            ],
        ]);

        static::assertSame([
            'dbname' => 'jadudb',
            'user' => 'jadu',
            'password' => 'password',
            'host' => 'localhost',
            'port' => '3306',
            'driver' => 'sqlsrv',
        ], $this->loader->load(vfsStream::url('root')));
    }

    public function testLoadDoesNotReturnConfigurationWhenDsnUsed()
    {
        $systemXml = <<<'XML'
<?xml version="1.0" encoding="utf-8" ?>
<system xmlns:config="http://www.jadu.co.uk/schema/config">
    <db_host>localhost</db_host>
    <db_port>3306</db_port>
    <db_username>jadu</db_username>
    <db_password>password</db_password>
    <db_name>jadudb</db_name>
    <db_dbms>pdo_mysql</db_dbms>
    <db_use_dsn>1</db_use_dsn>
</system>
XML;

        vfsStream::setup('root', null, [
            'config' => [
                'system.xml' => $systemXml,
            ],
        ]);

        static::assertSame([], $this->loader->load(vfsStream::url('root')));
    }
}
