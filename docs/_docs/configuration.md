---
layout: docs
---
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
        "version": "EXAMPLE_VERSION",
        "combine": {
            "jadu/cms": "15.0.0"
        }
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

* [patch](/docs/patch#configuration)
* [package](/docs/package#configuration)
* [migrations](/docs/migrations#configuration)
