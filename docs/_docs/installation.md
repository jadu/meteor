---
title: Installation
layout: docs
toc: true
---
All Continuum product packages will have a Phar archive of Meteor bundled within, however when deploying custom code
it may be beneficial to also use Meteor to create packages.

[Download the Phar archive from GitHub](https://github.com/jadu/meteor/releases/latest)

The PHP binary is required to run Meteor. For example:

    php meteor.phar

The Phar can also be made executable and run directly:

    mv meteor.phar meteor
    chmod +x meteor
    ./meteor
