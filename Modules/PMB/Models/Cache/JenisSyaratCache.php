<?php

namespace Modules\PMB\Models\Cache;

use Modules\Core\Models\SingleCacheModel;
use Modules\PMB\Models\SyaratJenis;

class JenisSyaratCache extends SingleCacheModel
{
    const KEY = 'jenis_syarat';
    const SECONDS = parent::WEEKS; // cache 1 minggu

    public static function getDefault()
    {
        return SyaratJenis::select('id', 'kode_jenis_syarat', 'nama_jenis_syarat')->get()->toArray();
    }
}
