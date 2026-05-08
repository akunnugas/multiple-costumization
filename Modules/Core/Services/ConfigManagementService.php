<?php

namespace Modules\Core\Services;

use Modules\Core\Models\Shared\ConfigCache;

class ConfigManagementService
{
    /**
     * Get shared config value.
     */
    public static function showSharedConfig($key = null)
    {
        $config = ConfigCache::find();

        if (empty($key)) {
            return $config;
        }

        return $config[$key] ?? null;
    }
}
