<?php

namespace Meteor\Migrations\Generator;

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class MigrationGeneratorTest extends TestCase
{
    private $migrationGenerator;

    protected function setUp(): void
    {
        $this->migrationGenerator = new MigrationGenerator();
    }

    public function testGenerate()
    {
        vfsStream::setup('root');

        $generatedCode = <<<'PHP'
<?php

namespace Migrations;

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

        static::assertSame($generatedCode, file_get_contents(vfsStream::url('root/Version201600701102030.php')));
    }

    public function testGenerateThrowsExceptionWhenMigrationsDirectoryDoesNotExist()
    {
        static::expectException(InvalidArgumentException::class);

        vfsStream::setup('root');

        $this->migrationGenerator->generate('201600701102030', 'Migrations', vfsStream::url('root/migrations'));
    }
}
