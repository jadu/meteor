# Patching with Meteor

## Applying a patch

To apply a patch run the following command from within the package directory:

    php meteor.phar patch:apply

You will be asked to provide the path to the Jadu install (i.e. /var/www/jadu on Unix systems).

## Rolling back a patch

To rollback a patch run the following command from within the package directory used to apply the patch:

    php meteor.phar patch:rollback

If there are backups available you will be provided with their details and asked to choose a backup to roll back to.
