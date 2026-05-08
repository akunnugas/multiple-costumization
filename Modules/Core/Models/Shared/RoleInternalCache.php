<?php

namespace Modules\Core\Models\Shared;

use Modules\Core\Extensions\Models\Abstracts\CacheModel;

class RoleInternalCache extends CacheModel
{
    const KEY = 'role_internal';
    const SHARED = true;

    /**
     * Get a record from database.
     */
    public static function findDefault($id)
    {
        return RoleInternal::select(['nama_role', 'kode_role'])->find($id)?->toArray();
    }
}
