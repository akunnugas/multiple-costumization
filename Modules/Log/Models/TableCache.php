<?php

namespace Modules\Log\Models;

use Modules\Core\Models\CacheModel;

class TableCache extends CacheModel
{
    const KEY = 'log';

    /**
     * Get a record from database.
     */
    public static function findDefault($id)
    {
        return Table::where('name', $id)->first(['id'])?->toArray();
    }
}
