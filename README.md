# Meteor

![CI](https://github.com/jadu/meteor/actions/workflows/test.yml/badge.svg)
[![MIT License](https://img.shields.io/github/license/jadu/meteor.svg)](https://github.com/jadu/meteor/blob/master/LICENSE.md)
![Downloads](https://img.shields.io/github/downloads/jadu/meteor/total.svg)

Meteor is a packaging and deployment tool for the Jadu Continuum platform.

## Compiling the Phar

The Phar file is compiled using the [`box` tool](http://box-project.github.io/box2/).
Once `box` has been installed run the following command:

    box build

## Creating a release

1. Test the `master` branch using the [meteor-server-test](http://leeroy.ntn.jadu.net/job/meteor-server-test/) job.
1. Create a new tag from the master branch.
1. Build the meteor.phar with the [meteor-phar-build](http://leeroy.ntn.jadu.net/job/meteor-phar-build/) job.
1. Download the built meteor.phar.
1. [Create a new release for the tag on GitHub](https://github.com/jadu/meteor/releases) and upload the meteor.phar.
1. Update the Meteor version in the internal `meteor-ant` project ([see package.xml](https://gitlab.hq.jadu.net/engineering/meteor-ant/blob/master/package.xml#L4)).
1. Create a new tag of `meteor-ant`, where the tag name matches the main Meteor version.

## Documentation

The documentation is hosted on GitHub Pages using Jekyll from the `master` branch in the `docs` directory.

## Design goals

- Extensible
- Easy to use
- Robust error handling
