<?php

namespace Meteor\Migrations\Connection\Platform;

use Doctrine\DBAL\Platforms\SQLServer2008Platform as BaseSQLServer2008Platform;
use Doctrine\DBAL\Schema\TableDiff;

class SQLServer2008Platform extends BaseSQLServer2008Platform
{
    /**
     * @param TableDiff $diff
     */
    public function getAlterTableSQL(TableDiff $diff)
    {
        // Mark all changed columns as having their default value changed so that Doctrine will
        //  drop and re-add the default constraints that would otherwise cause errors.
        foreach ($diff->changedColumns as $columnDiff) {
            if (!$columnDiff->hasChanged('default')) {
                // If the column doesn't have a default value Doctrine should not attempt to add
                //  a constraint so we should be safe in using this hack.
                $columnDiff->changedProperties[] = 'default';
            }
        }

        return parent::getAlterTableSQL($diff);
    }
}
