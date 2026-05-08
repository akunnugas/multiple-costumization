<?php

namespace Modules\Gate\Models;

use Modules\Core\Models\SingleCacheModel;

class ModulCache extends SingleCacheModel
{
    const KEY = 'modul';

    /**
     * Get a record from database.
     */
    public static function getDefault()
    {
        return Modul::orderByRaw('info_left')
            ->get(['id', 'kode_modul', 'nama_modul', 'apakah_aktif'])
            ->append('url_home')
            ->keyBy('kode_modul')
            ->toArray();
    }
}
