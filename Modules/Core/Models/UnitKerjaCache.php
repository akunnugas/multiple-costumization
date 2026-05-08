<?php

namespace Modules\Core\Models;

class UnitKerjaCache extends CacheModel
{
    const KEY = 'unit_kerja';

    /**
     * Get a record from database.
     */
    public static function findDefault($id)
    {
        return UnitKerja::select(['nama_unit', 'ref_key_siakad'])->find($id)?->toArray();
    }
}
