---
title: Applying a patch
layout: guide
toc: true
---
Each Continuum product package is created using the Meteor tool.
The package is a ZIP archive containing all of the required code files and a `meteor.phar` binary used to apply the patch.

## Instructions

Upload the package to your server in a location outside of the Jadu installation.

Unzip the package. There should now be a directory containing the package contents. This following files and directories should be present:

    /meteor.json.package
    /meteor.phar
    /migrations/
    /to_patch/

Change directory to the extracted directory and run the apply patch command

    php meteor.phar patch:apply

Meteor will now begin applying the patch to your installation using the default options.
For more detailed information on the options available please see the `patch:apply` command documentation.

View a recording of a patch being applied:
<script type="text/javascript" src="https://asciinema.org/a/7iz18307vbaaymu9tikdl9oi0.js" id="asciicast-7iz18307vbaaymu9tikdl9oi0" async></script>

## Multiple-server environments

When patching a multi-server environment it is recommended to only run the database migrations on one of the servers.
Use the `--skip-db-migrations` option to not run the migrations when applying the patch.

    php meteor.phar patch:apply --skip-db-migrations
