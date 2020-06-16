* Adds --log-dir option at patching stage to specify a different folder for logs ([#84](https://github.com/jadu/meteor/pull/84)).
* Adds a --limit-backups option at patching stage ([#83](https://github.com/jadu/meteor/pull/83)).
* Adds correction to permission reset command in Troubleshooting documentation ([#94](https://github.com/jadu/meteor/pull/94)).

## v3.1.0

* Move migration step before set permissions step ([#75](https://github.com/jadu/meteor/pull/75)).
* Display an error when using an unsupported PHP version ([#73](https://github.com/jadu/meteor/pull/73)).

## v3.0.3

* Fix issue with autoload paths not being found when creating a package ([#71](https://github.com/jadu/meteor/pull/71)).

## v3.0.2

* Fixes issue with permissions being set on non-package paths ([#69](https://github.com/jadu/meteor/pull/69)).

## v3.0.1

* Allow the manifest file to be missing ([#68](https://github.com/jadu/meteor/pull/68)).

## v3.0.0

Dropped PHP 5.3 support. The minimum required PHP version is now 5.6.

Updated Symfony components to 3.2.x.

* Output which scripts are being executed ([#55](https://github.com/jadu/meteor/pull/55)).
* Default to not ignoring unavailable migrations ([#54](https://github.com/jadu/meteor/pull/54)).
* Fix migration file path output in the success message ([#53](https://github.com/jadu/meteor/pull/53)).
* Check whether the path is a broken symlink ([#62](https://github.com/jadu/meteor/pull/62)).
* Allow autoload paths to be registered ([#63](https://github.com/jadu/meteor/pull/63)).
* Verify package contents before applying the patch ([#65](https://github.com/jadu/meteor/pull/65)).
* Add --default option to reset default permissions of files ([#67](https://github.com/jadu/meteor/pull/67)).

## v2.3.0

* Add's --skip-scripts option to skip patch script execution ([#38](https://github.com/jadu/meteor/pull/38)).
* Check required PHP version before patching ([#35](https://github.com/jadu/meteor/pull/35)).
* Fix issue with circular references when rolling back ([#42](https://github.com/jadu/meteor/pull/42)).
* Fix issue with incompatible backups causing fatal errors ([#43](https://github.com/jadu/meteor/pull/43)).

## v2.2.0

* Fixes a typo within an exception message within the generate migration command ([#28](https://github.com/jadu/meteor/pull/28)).
* Fixes the detection of a script recursion ([#29](https://github.com/jadu/meteor/pull/29)).
* Add's the ability to process scripts from within combined package scripts ([#32](https://github.com/jadu/meteor/pull/32)).
* Add's additional recursion checking for circular references ([#31](https://github.com/jadu/meteor/pull/31)).
* Improves migration configuration handling ([#33](https://github.com/jadu/meteor/pull/33)).

## v2.1.2

* Fixed issue with the user being asked to delete 0 backups ([#23](https://github.com/jadu/meteor/pull/23)).
* Allow migration commands to be run from the install ([#21](https://github.com/jadu/meteor/pull/21)).
* Prevent duplicate combined packages ([#19](https://github.com/jadu/meteor/pull/19)).

## v2.1.1

* Changed unexecuted migrations confirmation question default answer to yes ([#14](https://github.com/jadu/meteor/pull/14)).
* Fixed issue with the updating migration version files task taking too long ([#13](https://github.com/jadu/meteor/pull/13)).

## v2.1.0

* Improved error message when not specifying a package name in migration commands ([#10](https://github.com/jadu/meteor/pull/10)).
* Added `--skip-combine` option to skip package combining ([#11](https://github.com/jadu/meteor/pull/11)).

## v2.0.2

* Further fixes for permission setting on Windows. All directories now have inherited permissions.

## v2.0.1

* Fixed permission setting on Windows

## v2.0.0

Initial release
