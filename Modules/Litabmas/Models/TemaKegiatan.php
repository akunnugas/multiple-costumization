<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Litabmas\Database\factories\TemaKegiatanFactory;

class TemaKegiatan extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.tema_kegiatan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama_tema',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'nama_tema' => ['required' => true, 'maxlength' => 255, 'unique' => true], // Nama Tema Kegiatan
    ];

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'nama_tema asc';
    const OPTION_COLUMN = 'nama_tema';

    protected static function newFactory()
    {
        return TemaKegiatanFactory::new();
    }

    /**
     * Get tema kegiatan berdasarkan beberapa parameter.
     * Digunakan hanya untuk select option yg berupa key (id) dan value (nama_tema).
     *
     * @param int|null $idBidangIlmu
     * @param int|null $idKlasterPendanaan
     * @param int|null $idSumberPendanaan
     * @param string|null $kodeJenisPendanaan
     * @return array|null
     */
    public static function optionDynamic(
        int $idBidangIlmu = null,
        int $idKlasterPendanaan = null,
        int $idSumberPendanaan = null,
        string $kodeJenisPendanaan = null,
        int $idAgendaKegiatanPendaftaran = null,
    )
    {
        return static::select('litabmas.tema_kegiatan.id', 'litabmas.tema_kegiatan.nama_tema')
            ->leftJoin('litabmas.klaster_pendanaan_bidang_ilmu_tema as fcmt', 'fcmt.id_tema_kegiatan', '=', 'litabmas.tema_kegiatan.id')
            ->leftJoin('litabmas.klaster_pendanaan_bidang_ilmu as fcm', 'fcm.id', '=', 'fcmt.id_klaster_pendanaan_bidang_ilmu')
            ->leftJoin('litabmas.klaster_pendanaan as fc', 'fc.id', '=', 'fcm.id_klaster_pendanaan')
            ->leftJoin('litabmas.klaster_pendanaan_agenda_kegiatan as kpak', 'kpak.id_klaster_pendanaan', '=', 'fc.id')
            ->when($idBidangIlmu, fn ($query) => $query->where('fcm.id_bidang_ilmu', $idBidangIlmu))
            ->when($idKlasterPendanaan, fn ($query) => $query->where('fcm.id_klaster_pendanaan', $idKlasterPendanaan))
            ->when($idSumberPendanaan, fn ($query) => $query->where('fc.id_sumber_pendanaan', $idSumberPendanaan))
            ->when($kodeJenisPendanaan, fn ($query) => $query->where('fc.kode_jenis_pendanaan', $kodeJenisPendanaan))
            ->when($idAgendaKegiatanPendaftaran, function ($query, $idAgendaKegiatanPendaftaran) {
                return $query->where(function($query) use ($idAgendaKegiatanPendaftaran) {
                    return $query->where('kpak.id_agenda_kegiatan', $idAgendaKegiatanPendaftaran)
                        ->whereDate('kpak.waktu_mulai', '<=', now()->toDateString())
                        ->whereDate('kpak.waktu_selesai', '>=', now()->toDateString());
                });
            })
            ->orderBy('litabmas.tema_kegiatan.nama_tema', 'asc')
            ->pluck('litabmas.tema_kegiatan.nama_tema', 'litabmas.tema_kegiatan.id')
            ->toArray();
    }
}
