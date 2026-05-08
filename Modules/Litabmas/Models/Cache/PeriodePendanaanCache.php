<?php

namespace Modules\Litabmas\Models\Cache;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Models\SingleCacheModel;
use Modules\Litabmas\Models\PeriodePendanaan;

class PeriodePendanaanCache extends SingleCacheModel
{
    const KEY = 'periode_pendanaan';
    const KEY_ACTIVE_PERIOD = self::KEY . '_aktif';

    /**
     * Get a record from database.
     * Cache one day (default).
     */
    public static function getDefault()
    {
        return PeriodePendanaan::select(['id', 'tahun', 'tanggal_mulai', 'tanggal_akhir'])
            ->orderBy('periode')
            ->get();
    }

    /**
     * Get periode yang aktif (berdasarkan tanggal sekarang).
     *
     * @return mixed
     */
    public static function periodeAktif()
    {
        // yang dalam range tanggal sekarang
        $result = PeriodePendanaan::where('tanggal_mulai', '<=', now()->toDateTimeString())
            ->where('tanggal_akhir', '>=', now()->toDateTimeString())
            ->select('id', 'tahun', 'tanggal_mulai', 'tanggal_akhir')
            ->first();

        return Cache::remember(
            static::defineKey() . '_aktif',
            static::DAYS,
            fn () => $result
        );
    }
}
