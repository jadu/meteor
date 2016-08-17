# Upgrade from 1.x to 2.0

## Naming your project

All projects now require a name.

    {
        "name": "jadu/client-project"
    }

The name should be unique and not conflict with Jadu product names, e.g. `jadu/cms` or `jadu/xfp`. For this reason
it is reccomended to prefix the name with a organisation namespace, e.g. `spacecraft/pigeon-council`.

## Ant build scripts

The Ant scripts have been removed from Meteor and moved to a separate repository for
backwards compatibility with existing configurations.

Update your `composer.json` file to require `jadu/meteor-ant` rather than `jadu/meteor`.

    {
        "jadu/meteor-ant": "^2.0"
    }

Update your `build.xml` file to replace any references of `vendor/jadu/meteor/` with `vendor/jadu/meteor-ant/`.

All existing Ant targets should still work the same, however when creating a package the `meteor.phar` will be downloaded
from the release server as the built Phar binary is no longer committed into the Meteor repository.

## Creating packages

Meteor will now create packages itself rather than relying on Ant. This brings extra capabilities like being able to combine multiple
packages into a mega-package.

The fileset with in your `build.xml` should be copied over to the `meteor.json` config with some small changes.

Given the existing Ant fileset:

    <fileset id="fileset" dir="${basedir}">
        <include name="jadu/**" />
        <include name="public_html/**" />
        <include name="var/**" />
        <include name="config/permissions/custom" />
        <include name="CLIENT_VERSION" />
        <exclude name="tests" />
    </fileset>

Then the `meteor.json` config should be updated as follows:

    {
        "package": {
            "files": [
                "/jadu/**",
                "/public_html/**",
                "/var/**",
                "/config/permissions/custom",
                "/CLIENT_VERSION",
                "!/tests"
            ],
            "version": "CLIENT_VERSION"
        }
    }

Key differences:

* Paths need to be prefixed with a `/`.
* To exclude paths use the `!` prefix.
* Add a reference to the VERSION file.

Some projects may have used the default fileset `fileset.default-client`:

    <fileset id="fileset.default-client" dir="${basedir}">
        <include name="jadu/**" />
        <include name="public_html/**" />
        <include name="var/**" />
        <include name="config/permissions/custom" />
        <include name="CLIENT_VERSION" />
        <exclude name="public_html/site/styles/widget/**" />
    </fileset>

See the docs for more information on how to create packages.

## Patching

The `--path` and `--patch-path` options are now deprecated and `--install-dir` and `--working-dir` should be used instead.

For example:

    $ php meteor.phar patch:apply --install-dir=/var/www/jadu --working-dir=package

In most cases the options can be omitted as Meteor will use sensible defaults and ask the user for input.
