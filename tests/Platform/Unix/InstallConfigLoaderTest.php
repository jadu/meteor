<?php

namespace Meteor\Platform\Unix;

use org\bovigo\vfs\vfsStream;

class InstallConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    public $loader;

    public function setUp()
    {
        $this->loader = new InstallConfigLoader();
    }

    public function testLoad()
    {
        $installConf = <<<CONF
JADU_VERSION="1.12"
VERBOSE=
GNU="yes"
GNU_SWITCHES="-b"
APACHECTL="/usr/sbin/apachectl"
APACHE_USER="jadu-www"
APACHE_GROUP="jadu"
APACHE_SERVER_CONFIG_FILE="/etc/httpd/conf/httpd.conf"
APACHE_SERVER_CONFIG_DIR="/etc/httpd/conf"
APACHE_SERVER_VERSION="2.2.1"
APACHE_ERROR_LOG_FILENAME="apache_error_log"
APACHE_ACCESS_LOG_FILENAME="apache_access_log"
FCGI="yes"
SUEXEC="yes"
PHP_ERROR_LOG_FILENAME="php_log"
VHOST_CONFIG_FILE="xfp.local.conf"
VHOST_SSL_CONFIG_FILE="xfp.local-ssl.conf"
VHOST_SERVERNAME="xfp.local"
VHOST_SERVERALIAS="www.xfp.local"
JADU_SUEXEC_USER="jadu-www"
JADU_SUEXEC_GROUP="jadu-www"
JADU_SYSTEM_USER="jadu"
JADU_SYSTEM_GROUP="jadu"
JADU_SYSTEM_USER_HOME="/var/www/jadu"
JADU_HOME="/var/www/jadu"
DB_DBMS="mysql"
MYSQL_NDB="no"
MYSQL="/usr/bin/mysql"
MSSQLCLI=""
MSSQLCLI_HOST_CMD=""
SED="/bin/sed"
GREP="/bin/grep"
AWK="/bin/gawk"
TAR="/bin/gtar"
AUTO_DB_SETUP="yes"
XML_CONFIG_DIR="config"
SYSTEMXML="system.xml"
CONSTANTSXML="constants.xml"
DB_CONFIG_FILE="/var/www/jadu/config/system.xml"
DB_CLIENT_HOSTS="localhost"
CRONTAB="/usr/bin/crontab"
CRONTAB_CONFIG_FILE="configs/crontab"
CRONTAB_MARKER_START="<xfp.local>"
CRONTAB_MARKER_END="<xfp.local>"
DISTRO="redhat"
CONFIGS_DIR="./configs"
CONFIG_TEMPLATES="./config_templates"
SQL_TEMPLATES_PATH="jadu/install/shared"
SRC_DIR="./jadu"
RANDOM_PASSWORDS="no"
FINISHED_INSTALL_PHASES="check_config_templates"
PHPWRAPPER_FILE="php-wrapper"
CONF;

        vfsStream::setup('root', null, [
            'install.conf' => $installConf,
        ]);

        $config = $this->loader->load(vfsStream::url('root'));

        $this->assertTrue($config->isSuexec());
        $this->assertSame('jadu', $config->getUser());
        $this->assertSame('jadu', $config->getGroup());
        $this->assertSame('jadu-www', $config->getWebUser());
        $this->assertSame('jadu-www', $config->getWebGroup());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unable to open
     */
    public function testLoadThrowsExceptionWhenFileDoesNotExist()
    {
        vfsStream::setup('root');

        $this->loader->load(vfsStream::url('root'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unable to parse
     */
    public function testLoadThrowsExceptionWhenFileCannotBeParsed()
    {
        vfsStream::setup('root', null, [
            'install.conf' => '!',
        ]);

        $this->loader->load(vfsStream::url('root'));
    }
}
