<?php

namespace Modules\Litabmas\Models\Cache;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Models\SingleCacheModel;
use Modules\Litabmas\Models\JenisOutputPenelitian;

class JenisOutputPenelitianCache extends SingleCacheModel
{
    const KEY = 'jenis_output_penelitian';
    const KEY_OPTIONS = self::KEY . '_options';

    /**
     * Get a record from database.
     * Cache one day (default).
     */
    public static function getDefault()
    {
        return JenisOutputPenelitian::select(['id', 'nama_output'])
            ->orderBy('nama_output')
            ->get();
    }

    /**
     * Display options.
     * @return array
     */
    public static function options()
    {
        return Cache::remember(
            static::defineKey() . '_options',
            static::WEEKS,
            function () {
                $result = self::getDefault()
                    ->mapWithKeys(function ($item) {
                        return [$item->id => $item->nama_output];
                    });

                return $result->toArray();
            }
        );
    }
}
