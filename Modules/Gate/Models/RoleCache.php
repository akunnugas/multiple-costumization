<?php

namespace Modules\Gate\Models;

use Modules\Core\Models\CacheModel;

class RoleCache extends CacheModel
{
    const KEY = 'role';

    /**
     * Get a record from database.
     */
    public static function findDefault($id)
    {
        return Role::select(['nama_role', 'kode_role'])->find($id)?->toArray();
    }
}
