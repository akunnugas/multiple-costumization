<?php

namespace Modules\Litabmas\Models\Cache;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Models\SingleCacheModel;
use Modules\Litabmas\Models\BidangIlmu;

class BidangIlmuCache extends SingleCacheModel
{
    const KEY = 'bidang_ilmu';
    const KEY_OPTIONS = self::KEY . '_options';
    const SECONDS = self::WEEKS; // cache 1 minggu

    /**
     * Get a record from database.
     * Cache one week.
     *
     * @return Collection
     */
    public static function getDefault()
    {
        return BidangIlmu::select(['id', 'ref_key_siakad', 'nama_bidang_ilmu', 'info_parent', 'info_level', 'info_left', 'info_right'])->get();
    }

    /**
     * Menampilkan opsi untuk kebutuhan filter.
     * Key nya adalah id dan value nya adalah name.
     * Cache one week.
     *
     * @return mixed
     */
    public static function options()
    {
        return Cache::remember(
            static::defineKey() . '_options',
            static::WEEKS,
            function () {
                $orderColumn = BidangIlmu::OPTION_ORDER;
                $valueColumn = BidangIlmu::OPTION_COLUMN;

                return BidangIlmu::orderByRaw($orderColumn)
                    ->get(['id', $valueColumn])
                    ->pluck($valueColumn, 'id')
                    ->toArray();
            }
        );
    }
}
