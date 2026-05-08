<?php

namespace Modules\Core\Models;

use Illuminate\Support\Facades\Cache;

abstract class CacheModel
{
    const KEY = 'cache';
    const SECONDS = 86400; // 1 hari
    const SHARED = false;

    public static function find($id)
    {
        return Cache::remember(static::defineIdKey($id), static::SECONDS, fn () => static::findDefault($id));
    }

    public static function destroy($id)
    {
        return Cache::forget(static::defineIdKey($id));
    }

    abstract public static function findDefault($id);

    protected static function defineIdKey($id)
    {
        $key = static::KEY . '-' . $id;
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
