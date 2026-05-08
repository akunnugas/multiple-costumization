<?php

namespace Modules\DMS\Models;

use Modules\Core\Models\CacheModel;
use Modules\DMS\Models\Dokumen;

class DokumenCache extends CacheModel
{
    const KEY = 'dms.dokumen';

    /**
     * Get a record from database.
     */
    public static function findDefault($slug)
    {
        return Dokumen::where('slug', $slug)->first();
    }
}
