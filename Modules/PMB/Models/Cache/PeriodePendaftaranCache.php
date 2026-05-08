<?php

namespace Modules\PMB\Models\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\SingleCacheModel;
use Modules\PMB\Models\PeriodePendaftaran;
use Modules\PMB\Services\PeriodePendaftaranManagementService;

class PeriodePendaftaranCache extends SingleCacheModel
{
    const KEY = 'periode_pendaftaran';

    /**
     * Get a record from database.
     */
    public static function getDefault()
    {
        return PeriodePendaftaran::select(['id', 'nama_periode'])->get()->toArray();
    }

    /**
     * Get active registration period.
     *
     * @return mixed
     * @old: getActive() in spmb/models/m_periodedaftar.php
     */
    public static function getActiveRegistrationPeriod()
    {
        $sql = (new PeriodePendaftaranManagementService())->getRelationRawQuery();

        // TODO: statusnya yg aktif tpi adanya draft dan published
        $defaultFilter = "rp.waktu_dihapus IS NULL
            AND rp.status_periode = '" . PeriodePendaftaran::STATUS_PUBLISHED . "'";

        $defaultOrder = [
            'field' => 'case when now() between coalesce(rp.waktu_dibuka,now()) and coalesce(rp.waktu_ditutup,now()) then 1 end asc,
                rp.waktu_dibuka, rpa.nama_jalur',
        ];

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            defaultOrder: $defaultOrder,
            defaultFilter: $defaultFilter
        );

        $resultQuery = DB::connection()->select($sql, $bindings);
        $arrayData = json_decode(json_encode($resultQuery), true);

        return Cache::remember(static::defineKey(), static::DAYS, fn () => $arrayData);
    }
}
