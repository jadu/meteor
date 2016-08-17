# Configuration

Meteor is configured through a JSON config file found in the working directory.

## Resolving the config file

Meteor will look for the config files in the following order:

* `meteor.json.package`
* `meteor.json`
* `meteor.json.dist`

## Structure

```json
{
    "name": "jadu/example",
    "patch": {
        "strategy": "overwrite"
    },
    "package": {
        "files": [
            "/**"
        ],
        "version": "VERSION",
        "combine": ["jadu/cms"]
    },
    "migrations": {
        "name": "CMS",
        "table": "JaduMigrations",
        "namespace": "Jadu\\Migrations",
        "directory": "migrations"
    },
    "extensions": []
}
```

Each extension (including standard extensions) have their own configuration section.

More information about each section:

* [patch](patch.md#configuration)
* [package](package.md#configuration)
* [migrations](migrations.md#configuration)
