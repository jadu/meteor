# Packaging

## Creating a package

```
php meteor.phar package
```

The output directory will default to `./output` and the filename will be generated from the name and version configuration.
For example if the name of your package is `jadu/test` and have a version file containing `1.0.0` then your package will be
named `jadu_test_1.0.0`. The ZIP archive will contain a directory called `jadu_test_1.0.0`.

A different output directory can be specified:

```
php meteor.phar package --output-dir=packages
```

A different package filename can be specified:

```
php meteor.phar package --filename=test_package
```

A package name must begin with a letter or number and can contain only letters, numbers, hyphens, underscores and dots. If an invalid
package filename is provided then Meteor will ignore the option and use the default filename.

## Configuration

```json
{
    "package": {
        "files": [
            "/**"
        ],
        "version": "VERSION",
        "combine": ["jadu/cms"]
    }
}
```

### files (optional)

The files section is a list of paths to include within the package.

Paths can also be excluded from the package by prefixing the path with an `!`, for example `!/composer.json` will exclude `/composer.json` from the package.

### version (optional)

The version section specifies the name of the file containing the version information for this package.

### combine (optional)

The combine section is a list of packages that must be passed in to the `--combine` option when creating a package.

## Combining packages

When building a package multiple other packages can be combined with it. The benefit of doing so is that only a single patch
needs to be applied.

```
php meteor.phar package --combine=cms.zip --combine=module.zip
```

In the above example all of the required files and migrations from `cms.zip` and `module.zip` will be included in the package.
