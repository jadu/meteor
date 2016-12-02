---
layout: docs
---
# Migrations

There are two different types of migrations, database migrations and file migrations.

## Database migrations

Database migrations are used to modify the database schema and data.

The status of the migrations is stored in database tables

```
php meteor.phar migrations:migrate
```

## File migrations

File migrations are used to modify files, e.g. config files.

The status of file migrations is stored in a file on each server.

```
php meteor.phar file-migrations:migrate
```

Note: With a multiple server environment without a shared filesystem it is important to run the file migrations on all servers.

## Configuration

```json
{
    "migrations": {
        "name": "CMS",
        "table": "JaduMigrations",
        "namespace": "Jadu\\Migrations",
        "directory": "migrations"
    }
}
```

### name (optional)

Defaults to: `Migrations`

### table (required)

The table must be different for each product/module.

### namespace (optional)

Defaults to: `DoctrineMigrations`

The namespace should be different for each product/module to avoid collisions when running multiple sets of migrations in a single patch.

### directory (optional)

Defaults to: `upgrades/migrations`

## Status of migrations

To view the status of migrations run the following command:

```
php meteor.phar migrations:status
```

## Generating migrations

To generate a new migration class to be populated run the following command:

```
php meteor.phar migrations:generate
```
