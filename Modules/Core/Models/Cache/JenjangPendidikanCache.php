<?php

namespace Modules\Core\Models\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\SingleCacheModel;

class JenjangPendidikanCache extends SingleCacheModel
{
    const KEY = 'jenjang_pendidikan';

    /**
     * Get a record from database.
     */
    public static function getDefault()
    {
        return JenjangPendidikan::select(['id', 'kode_jenjang', 'nama_jenjang'])->get()->toArray();
    }

    /**
     * Menampilkan opsi untuk kebutuhan filter.
     *
     * @param bool $isUniv
     * @return mixed
     * @old: getListCombo() di models/m_jenjang.php
     */
    public static function options(bool $isUniv = true)
    {
        return Cache::remember(
            static::defineKey(),
            static::MONTHS,
            function () use ($isUniv) {
                $sql = "select d.id, d.kode_jenjang||' - '||d.nama_jenjang as name from " . (new JenjangPendidikan)->getTable()
                    . " as d where waktu_dihapus is null";

                if ($isUniv) { // FIXME: is_univ true tapi column nggk ada
                    $sql .= " and d.apakah_pt = true";
                }

                $sql .= " order by d.urutan";
                $result = DB::select($sql);

                $data = array_column($result, 'name', 'id');
                return $data;
            }
        );
    }
}
