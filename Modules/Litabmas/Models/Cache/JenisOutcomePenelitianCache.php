<?php

namespace Modules\Litabmas\Models\Cache;

use Modules\Core\Models\SingleCacheModel;
use Modules\Litabmas\Models\JenisOutcomePenelitian;

class JenisOutcomePenelitianCache extends SingleCacheModel
{
    const KEY = 'jenis_outcome_penelitian';

    /**
     * Get a record from database.
     * Cache one day (default).
     */
    public static function getDefault()
    {
        return JenisOutcomePenelitian::select(['jenis_outcome_penelitian.id', 'jenis_outcome_penelitian.nama_outcome', 'jenis_outcome_penelitian.kategori_outcome',
            'jenis_outcome_penelitian.batas_pengumpulan_outcome', 'so.id as id_jenis_publikasi', 'so.nama_jenis_publikasi'])
            ->join('litabmas.jenis_publikasi as so', 'so.id', '=', 'litabmas.jenis_outcome_penelitian.id_jenis_publikasi')
            ->orderBy('jenis_outcome_penelitian.nama_outcome')
            ->get();
    }
}
