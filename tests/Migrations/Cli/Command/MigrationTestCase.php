<?php

namespace Meteor\Migrations\Cli\Command;

use Meteor\Cli\Command\CommandTestCase;
use Meteor\Migrations\ServiceContainer\MigrationsExtension;

abstract class MigrationTestCase extends CommandTestCase
{
    protected $extension;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new MigrationsExtension();
    }
}
