<?php

namespace Meteor\Migrations\Generator;

use org\bovigo\vfs\vfsStream;

class MigrationGeneratorTest extends \PHPUnit_Framework_TestCase
{
    private $migrationGenerator;

    public function setUp()
    {
        $this->migrationGenerator = new MigrationGenerator();
    }

    public function testGenerate()
    {
        vfsStream::setup('root');

        $generatedCode = <<<'PHP'
<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version201600701102030 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
PHP;

        $this->migrationGenerator->generate('201600701102030', 'Migrations', vfsStream::url('root'));

        $this->assertSame($generatedCode, file_get_contents(vfsStream::url('root/Version201600701102030.php')));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGenerateThrowsExceptionWhenMigrationsDirectoryDoesNotExist()
    {
        vfsStream::setup('root');

        $this->migrationGenerator->generate('201600701102030', 'Migrations', vfsStream::url('root/migrations'));
    }
}
