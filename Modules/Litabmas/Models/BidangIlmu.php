<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Litabmas\Models\Cache\BidangIlmuCache;

class BidangIlmu extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.bidang_ilmu';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'ref_key_siakad',
        'nama_bidang_ilmu',
        'info_parent',
        'info_level',
        'info_left',
        'info_right',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'ref_key_siakad' => ['required' => true, 'maxlength' => 255], // Kolom PK SIAKAD V1
        'nama_bidang_ilmu' => ['required' => true, 'maxlength' => 255], // Nama Bidang Ilmu
        'info_parent' => ['required' => false, 'maxlength' => 255], // Parent Bidang Ilmu
        'info_level' => ['required' => false], // Level Bidang Ilmu
        'info_left' => ['required' => false], // Info Left
        'info_right' => ['required' => false], // Info Right
    ];

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'nama_bidang_ilmu asc';
    const OPTION_COLUMN = 'nama_bidang_ilmu';

    /**
     * Display options.
     * @return array
     */
    public static function options()
    {
        // FIX ME: Mekanisme cache belum berjalan optimal
        // return BidangIlmuCache::options();

        $orderColumn = BidangIlmu::OPTION_ORDER;
        $valueColumn = BidangIlmu::OPTION_COLUMN;

        return BidangIlmu::orderByRaw($orderColumn)
            ->get(['id', $valueColumn])
            ->pluck($valueColumn, 'id')
            ->toArray();
    }

    /**
     * Clear all cache from this model.
     *
     * @return void
     */
    public static function clearAllCache()
    {
        BidangIlmuCache::destroy();
        BidangIlmuCache::destroyCustomKey(BidangIlmuCache::KEY_OPTIONS);
    }

    /**
     * Get bidang ilmu berdasarkan beberapa parameter.
     * Digunakan hanya untuk select option yg berupa key (id) dan value (nama_bidang_ilmu).
     *
     * @param int|null $idTemaKegiatan
     * @param int|null $idKlasterPendanaan
     * @param int|null $idSumberPendanaan
     * @param string|null $kodeJenisPendanaan
     * @return array|null
     */
    public static function optionDynamic(
        int $idTemaKegiatan = null,
        int $idKlasterPendanaan = null,
        int $idSumberPendanaan = null,
        string $kodeJenisPendanaan = null,
        int $idAgendaKegiatanPendaftaran = null,
    )
    {
        return static::select('litabmas.bidang_ilmu.id', 'litabmas.bidang_ilmu.nama_bidang_ilmu')
            ->leftJoin('litabmas.klaster_pendanaan_bidang_ilmu as fcm', 'fcm.id_bidang_ilmu', '=', 'litabmas.bidang_ilmu.id')
            ->leftJoin('litabmas.klaster_pendanaan_bidang_ilmu_tema as fcmt', 'fcmt.id_klaster_pendanaan_bidang_ilmu', '=', 'fcm.id')
            ->leftJoin('litabmas.klaster_pendanaan as fc', 'fc.id', '=', 'fcm.id_klaster_pendanaan')
            ->leftJoin('litabmas.klaster_pendanaan_agenda_kegiatan as kpak', 'kpak.id_klaster_pendanaan', '=', 'fc.id')
            ->when($idTemaKegiatan, fn($query) => $query->where('fcmt.id_tema_kegiatan', $idTemaKegiatan))
            ->when($idKlasterPendanaan, fn($query) => $query->where('fcm.id_klaster_pendanaan', $idKlasterPendanaan))
            ->when($idSumberPendanaan, fn($query) => $query->where('fc.id_sumber_pendanaan', $idSumberPendanaan))
            ->when($kodeJenisPendanaan, fn($query) => $query->where('fc.kode_jenis_pendanaan', $kodeJenisPendanaan))
            ->orderBy('litabmas.bidang_ilmu.nama_bidang_ilmu', 'asc')
            ->pluck('litabmas.bidang_ilmu.nama_bidang_ilmu', 'litabmas.bidang_ilmu.id')
            ->toArray();
    }
}
