# Upgrade from 3.x to 4.0

## Migration update

4.0 introduces a newer version of Doctrine which includes a change to the namespacing of the AbstractMigration class.

If you have any migrations in your project that reference this class, the namespace reference will need to be updated.

Change from:


```
Doctrine\DBAL\Migrations\AbstractMigration
```

to:

```
Doctrine\Migrations\AbstractMigration
```

The method signatures for many methods within this class have also changed. Migrations extending this abstract will therefore also need to be updated, for example changing from:

```
public function up(Schema $schema)
```

to:

```
public function up(Schema $schema): void
```

[A script is available to apply these changes to all migrations within a project.](https://gist.github.com/DenisYaschuk/d3ade2d88d058cf9c971cf9d1f580a0f)
