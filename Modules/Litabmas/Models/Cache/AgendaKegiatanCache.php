<?php

namespace Modules\Litabmas\Models\Cache;

use Modules\Core\Models\SingleCacheModel;
use Modules\Litabmas\Models\AgendaKegiatan;

class AgendaKegiatanCache extends SingleCacheModel
{
    const KEY = 'agenda_kegiatan';
    const SECONDS = self::WEEKS; // cache 1 minggu

    /**
     * Get a record from database.
     */
    public static function getDefault()
    {
        return AgendaKegiatan::select(['id', 'kode_agenda', 'nama_agenda', 'apakah_wajib', 'urutan'])
            ->orderBy('urutan')
            ->get();
    }
}
