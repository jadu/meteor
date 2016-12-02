---
layout: docs
---
# Scripts

A script is a command-line executable command that can be run by Meteor during command processes.

## Events

* patch.pre-apply
* patch.post-apply
* patch.pre-rollback
* patch.post-rollback

## Configuration

Scripts are configured in the `meteor.json` config file.

```json
{
    "scripts": {
        "hello": "say -v Ralph 'Hello world'"
    }
}
```

Each script must have a unique name and a command-line executable command. If required an array of commands can also be provided.

When scripts are executed with the `run` command the current working directory set as the `--working-dir` option value.
However, when scripts are executed with the `patch:apply` or `patch:rollback` commands then the  `--install-dir` option value is used as the current working directory instead.

Multiple scripts can be run for an event:

```json
{
    "scripts": {
        "patch.post-apply": ["@clear-cache", "@rebuild-htaccess"],
    }
}
```

## Referencing scripts

To enable script re-use and avoid duplication, scripts can be referenced from another script by prefixing the name with an `@` symbol.

```json
{
    "scripts": {
        "patch.post-apply": "@clear-cache",
        "clear-cache": "clear-cache.sh"
    }
}
```
