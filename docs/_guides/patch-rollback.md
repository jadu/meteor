---
title: Rolling back a failed patch
layout: guide
toc: true
---
If a patch went wrong and could not complete you can roll back to the latest backup (created by Meteor when patching) using the `patch:rollback` command.

## Instructions

The command should be run from within the package that needs to be rolled back.

```
cd package
php meteor.phar patch:rollback
```

Meteor will find the most recent compatible backup then restore the backed up files and migrate down the database/file migrations. If there are multiple backups available
to rollback to then Meteor will ask you to choose a backup. If the most recent backup is not selected then the intermediate backups will be removed after the rollback has completed.

After rolling back a release it is recommended to re-apply the patch for the current version. For example when rolling back from `1.2.0` to `1.0.0` then patch again with `1.0.0`.
The reason for doing so is to ensure that all of the files for `1.0.0` exist on the server. Some file migrations may have run that would delete files that no longer
exist in the newer version and the backup would not contain those deleted files.

View a recording of a patch being rolled back:
<script type="text/javascript" src="https://asciinema.org/a/403zitlz5dly1gc8ksgr7cu4c.js" id="asciicast-403zitlz5dly1gc8ksgr7cu4c" async></script>

## Multiple-server environments

When rolling back a patch on a multi-server environment it is recommended to only run the database down migrations on one of the servers.
Use the `--skip-db-migrations` option to not run the migrations when applying the patch.

    php meteor.phar patch:rollback --skip-db-migrations
