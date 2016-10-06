## v2.2.0

* Fixes a typo within an exception message within the generate migration command. ([#28](https://github.com/jadu/meteor/pull/28))
* Fixes the detection of a script recursion. ([#29](https://github.com/jadu/meteor/pull/29))
* Add's the ability to process scripts from within combined package scripts. ([#32](https://github.com/jadu/meteor/pull/32))
* Add's additional recursion checking for circular references. ([#31](https://github.com/jadu/meteor/pull/31))
* Improves migration configuration handling. ([#33](https://github.com/jadu/meteor/pull/33))

## v2.1.2

* Fixed issue with the user being asked to delete 0 backups ([#23](https://github.com/jadu/meteor/pull/23))
* Allow migration commands to be run from the install ([#21](https://github.com/jadu/meteor/pull/21))
* Prevent duplicate combined packages ([#19](https://github.com/jadu/meteor/pull/19))

## v2.1.1

* Changed unexecuted migrations confirmation question default answer to yes ([#14](https://github.com/jadu/meteor/pull/14))
* Fixed issue with the updating migration version files task taking too long ([#13](https://github.com/jadu/meteor/pull/13))

## v2.1.0

* Improved error message when not specifying a package name in migration commands ([#10](https://github.com/jadu/meteor/pull/10))
* Added `--skip-combine` option to skip package combining ([#11](https://github.com/jadu/meteor/pull/11))

## v2.0.2

* Further fixes for permission setting on Windows. All directories now have inherited permissions.

## v2.0.1

* Fixed permission setting on Windows

## v2.0.0

Initial release
