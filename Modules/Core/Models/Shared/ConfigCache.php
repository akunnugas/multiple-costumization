<?php

namespace Modules\Core\Models\Shared;

use Modules\Core\Extensions\Models\Abstracts\SingleCacheModel;

class ConfigCache extends SingleCacheModel
{
    const KEY = 'config';
    const SHARED = true;

    /**
     * Get a record from database.
     */
    public static function getDefault()
    {
        $data = Config::find(Config::ID)?->toArray();

        // konversi
        if (!empty($data['ip_debug'])) {
            $data['ip_debug'] = json_decode($data['ip_debug'], true);
        }

        return $data;
    }
}
