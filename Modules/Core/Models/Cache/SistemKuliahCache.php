<?php

namespace Modules\Core\Models\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\SistemKuliah;
use Modules\Core\Models\SingleCacheModel;

class SistemKuliahCache extends SingleCacheModel
{
    const KEY = 'sistem_kuliah';

    /**
     * Get a record from database.
     */
    public static function getDefault()
    {
        return SistemKuliah::select(['id', 'nama_sistem', 'deskripsi_sistem'])->get()->toArray();
    }

    public static function options()
    {
        $sql = "select id, nama_sistem from " . (new SistemKuliah)->getTable()
            . " where waktu_dihapus is null";

        $sql .= " order by " . SistemKuliah::OPTION_ORDER;
        $result = DB::select($sql);

        $data = array_column($result, 'nama_sistem', 'id');

        return Cache::remember(
            static::defineKey(),
            static::MONTHS,
            fn () => $data
        );
    }
}
