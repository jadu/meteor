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
