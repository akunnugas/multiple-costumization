<?php

namespace Modules\Core\Extensions\Models\Abstracts;

use Illuminate\Support\Facades\Cache;

abstract class SingleCacheModel
{
    const KEY = 'cache';
    const SECONDS = self::DAYS; // bisa di override, default 1 hari
    const DAYS = 86400; // 1 hari
    const WEEKS = 604800; // 7 hari
    const MONTHS = 2592000; // 30 hari
    const SHARED = false;

    public static function get()
    {
        return Cache::remember(static::defineKey(), static::SECONDS, fn () => static::getDefault());
    }

    public static function destroy()
    {
        Cache::forget(static::defineKey());
    }

    public static function destroyCustomKey($key)
    {
        Cache::forget($key);
    }

    abstract public static function getDefault();

    protected static function defineKey()
    {
        $key = static::KEY;
        if (static::SHARED) {
            return $key;
        }

        $clientId = request()->client['id'] ?? null;
        if ($clientId) {
            $key = $clientId . '-' . $key;
        }

        return $key;
    }
}
