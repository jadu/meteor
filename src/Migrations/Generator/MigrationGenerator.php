<?php

namespace Meteor\Migrations\Generator;

use InvalidArgumentException;

class MigrationGenerator
{
    /**
     * @param string $version
     * @param string $namespace
     * @param string $path
     */
    public function generate($version, $namespace, $path)
    {
        $template = <<<'PHP'
<?php

namespace <namespace>;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version<version> extends AbstractMigration
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

        $code = str_replace(
            [
                '<namespace>',
                '<version>',
            ],
            [
                $namespace,
                $version,
            ],
            $template
        );

        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf('Migrations directory `%s` does not exist.', $path));
        }

        file_put_contents($path.'/Version'.$version.'.php', $code);
    }
}
