<?php

namespace Modules\Core\Models\Cache;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Models\SingleCacheModel;
use Modules\Core\Models\Wilayah;

class WilayahCache extends SingleCacheModel
{
    const KEY = 'wilayah_cache';

    /**
     * Get a record from database.
     */
    public static function getDefault()
    {
        return Wilayah::select(['id', Wilayah::OPTION_COLUMN])->get()->toArray();
    }

    /**
     * Display options by region level and parent id.
     *
     * @param int $level
     * @param int|null $parentId
     * @param string $orderBy
     * @return array
     */
    public static function optionsByLevel(int $level, int|string $parentId = null, string $orderBy = Wilayah::OPTION_ORDER)
    {
        $data = Wilayah::query()
            ->where('level_wilayah', $level)
            ->when($parentId, function ($query, $parentId) {
                return $query->where('id_parent', $parentId);
            })
            ->orderByRaw($orderBy)
            ->get(['id', Wilayah::OPTION_COLUMN])
            ->pluck(Wilayah::OPTION_COLUMN, 'id')
            ->toArray();

        return Cache::remember(
            static::defineKey()."-$level-".($parentId ?? 0)."-$orderBy",
            static::WEEKS,
            fn () => $data
        );
    }
}
