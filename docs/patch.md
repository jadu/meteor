# Patching

## Applying a patch

A Meteor package is a ZIP archive containing all of the required code files and a `meteor.phar` binary used to apply the patch.

Once a package has been uploaded and extracted on the server it can be applied using the `patch:apply` command.

```
cd package
php meteor.phar patch:apply
```

Meteor will now begin applying the patch to your installation using the default options.

## Rolling back a patch

If a patch went wrong and could not complete you can roll back to the latest backup (created by Meteor when patching) using the `patch:rollback` command.

The command should be run from within the package that needs to be rolled back.

```
cd package
php meteor.phar patch:rollback
```

Meteor will find the most recent compatible backup then restore the backed up files and migrate down the database/file migrations. If there are multiple backups available
to rollback to then Meteor will ask you to choose a backup. If the most recent backup is not selected then the intermediate backups will be removed after the rollback has completed.

After rolling back a release it is reccomended to re-apply the patch for the current version. For example when rolling back from `1.2.0` to `1.0.0` then patch again with `1.0.0`.
The reason for doing so is to ensure that all of the files for `1.0.0` exist on the server. Some file migrations may have run that would delete files that no longer
exist in the newer version and the backup would not contain those deleted files.

## Skipping migrations

To apply or rollback a patch without executing migrations down the following two options are availabe, `--skip-db-migrations` and `--skip-file-migrations`.

```
php meteor.phar patch:apply --skip-db-migrations
php meteor.phar patch:rollback --skip-db-migrations
```

The `--skip-db-migrations` option would most commonly be used when patching load balanced environments where the migrations do not need to be run on every node.

```
php meteor.phar patch:apply --skip-file-migrations
php meteor.phar patch:rollback --skip-file-migrations
```

## Configuration

```json
{
    "patch": {
        "strategy": "overwrite"
    }
}
```

### patch (optional)

Defaults to: `overwrite`
