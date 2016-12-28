---
title: Autoload paths
layout: docs
toc: true
---
It is possible to add additional autoload paths to the Meteor class loader. This may be useful when writing custom migration base classes or helpers.

## Composer paths

The `composer` path type will load the `composer.json` of the specified Composer packages and add any `psr-0`, `psr-4` or `classmap` paths it finds.

```json
{
    "autoload": {
        "composer": ["jadu/test"]
    }
}
```

## PSR-4 paths

The `psr-4` path type will add the given prefix and paths to the class loader.

```json
{
    "autoload": {
        "psr-4": {
            "Jadu\\": "jadu/"
        }
    }
}
```

The paths added to the class loader will be prepended with the package root path, e.g. `/path/to/package/to_patch`.
