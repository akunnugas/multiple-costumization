<?php

namespace Modules\Core\Models\Traits;

trait ClearCache
{
    protected static function bootClearCache()
    {
        static::updated(function ($model) {
            static::clearCache($model);
        });
        static::deleted(function ($model) {
            static::clearCache($model);
        });
    }

    /**
     * Clear any related cache
     * 
     * @param mixed $model
     */
    abstract private static function clearCache($model);
}
