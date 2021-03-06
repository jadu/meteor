---
title: Troubleshooting
layout: docs
toc: true
---

### Meteor is locked

```
Unable to create lock file. This may be due to failure during a previous attempt to apply this package.
```

This error means that either a patch is currently in progress or the patch process was halted before it could finish. If you can be certain that a patch is not in progress (check active processes) then the lock file can be cleared with the following command:

```
php meteor.phar patch:clear-lock
```

After the lock file is cleared you can try the patch again.

### Package verification errors

```
The patch cannot be applied as the package could not be verified against the manifest.
```

This error means that some of the files from the package were not extracted correctly or Meteor could not read them. The files that are either missing or different will be displayed in a list below the error. To resolve this issue try extracting the package again.

On some Windows environments if the package is being extracted at a path that is too long (i.e. lots of nested directories with long names) then the ZIP may fail to be extracted. In this case try extracting the package in a higher level or shorter named directory.

### Previously executed migrations that are not registered migrations

```
Running database migrations

! [NOTE] You have 2 previously executed migrations that are not registered migrations.

* 2016-11-30 00:00:00 (20161130000000)
* 2016-11-30 00:00:01 (20161130000001)
```

This warning means that some migrations that were previously executed do not exist within the package. This may be because they were deleted or potentially renamed. If these are core migrations (`jadu/cms` or `jadu/xfp`) then check with Support as this should not happen. To avoid this happening do not rename migration verisons after a patch has been applied. If you need to remove a migration then simply empty the `up` and `down` methods.

### Database connection error

If the first stages of deployment take a long time (e.g. due to network latency if the files are on an NFS share) then it is possible that the database connection may timeout before the migrations are applied. In this case you may see an error such as:

```
Error: PDOException
SQLSTATE[HY000]: General error: 2006 MySQL server has gone away
```

After creating an automatic back-up it will initially connect to the database to store the current progress of migrations in the back-up. If it has stalled at this stage then the last notice before the error will be:

```
Storing current migration status in the backup
```

You will need to first clear the lock file, and then re-apply the package; skipping the backup step, which should have completed successfully in the previous run:

```
php meteor.phar patch:clear-lock
php meteor.phar patch:apply --skip-backup
```

If it has progressed passed this, and has copied the files from the package as well then again you will need to clear the lock file; but this time you will need to specifically perform migrations, file migrations, setting of permissions, and any standard post-patch actions you may take such as clearing and/or warming of cache.

```
php meteor.phar patch:clear-lock
php meteor.phar migrations:migrate
php meteor.phar file-migrations:migrate
php meteor.phar permissions:reset
```

*Note:* If the file copying and/or back-up was slow enough for the database connection to time-out, then the `permissions:reset` command will also take a similar amount of time to complete.
