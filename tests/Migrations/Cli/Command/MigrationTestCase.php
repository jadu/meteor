<?php

namespace Meteor\Migrations\Cli\Command;

use Meteor\Cli\Command\CommandTestCase;
use Meteor\Migrations\ServiceContainer\MigrationsExtension;

abstract class MigrationTestCase extends CommandTestCase
{
    public $extension;

    public function setUp()
    {
        parent::setUp();
        $this->extension = new MigrationsExtension();
    }
}
