<?php

namespace Modules\PMB\Models\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\SingleCacheModel;
use Modules\PMB\Models\SebaranProdi;

class SebaranProdiCache extends SingleCacheModel
{
    const KEY = 'sebaran_prodi';

    /**
     * Get a record from database.
     */
    public static function getDefault()
    {
        return SebaranProdi::select(['id', 'id_unit_kerja', 'id_jenjang_pendidikan'])->get()->toArray();
    }

    /**
     * Dapetin list program studi berdasarkan periode pendaftaran.
     *
     * @param array $registrationPeriodIds
     * @return array
     * @old: getByPeriode in spmb/models/m_sebaranprodi.php
     */
    public static function getListByRegistrationPeriodIds(array $registrationPeriodIds)
    {
        // TODO: belum join ke universitas prodi (dipake utk coalesce)
        // yang lama: coalesce(u.namaunit,up.namaprodi) namaunit, coalesce(u.idjenjang,up.idjenjang) idjenjang

        if(empty($registrationPeriodIds)) {
            return [];
        }

        // pastiin di index dari 0
        $registrationPeriodIds = array_values($registrationPeriodIds);
        $inClauseRegistrationPeriodIds = implode(',', array_fill(0, count($registrationPeriodIds), '?'));

        $query = "select pd.id, pd.daya_tampung, o.id_jenjang_pendidikan, d.kode_jenjang,
                d.nama_jenjang, pd.id_unit_kerja, o.nama_unit
            from pmb.sebaran_prodi pd
            join core.unit_kerja o on o.id = pd.id_unit_kerja and o.waktu_dihapus is null
            join core.jenjang_pendidikan d on d.id = o.id_jenjang_pendidikan and d.waktu_dihapus is null
            where pd.waktu_dihapus is null
                and pd.id_periode_pendaftaran in ($inClauseRegistrationPeriodIds)";

        $resultQuery = DB::select($query, $registrationPeriodIds);
        $arrayData = json_decode(json_encode($resultQuery), true); // jadikan array

        return Cache::remember(static::defineKey(), static::WEEKS, fn () => $arrayData);
    }
}
