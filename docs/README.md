# Meteor

Meteor is a packaging and deployment tool for the Jadu Continuum platform.

* [Patching](patch.md)
* [Packaging](package.md)
* [Migrations](migrations.md)
* [Scripts](scripts.md)
* [Configuration](configuration.md)
* [Extensions](extensions.md)

## Installation

All Continuum product packages will have a Phar archive of Meteor bundled within, however when deploying custom code
it may be benefiticial to also use Meteor to create packages. To do so you can install Meteor in one of two ways.

Download the Phar archive (recommended):

```
$ wget http://someurl/meteor.phar
$ chmod +x meteor.phar
$ mv meteor.phar /usr/local/bin/meteor
```

Install via Composer:

```
$ composer require jadu/meteor
```

When installed via Composer the `meteor` binary will be available in your vendor binary directory (defaulting to `vendor/bin`).
However, when a package is created it will not bundle meteor.phar unless the path to the Phar is specified using the `--phar` option.
