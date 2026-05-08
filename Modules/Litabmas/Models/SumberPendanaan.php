<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;
use Modules\Litabmas\Database\factories\SumberPendanaanFactory;

class SumberPendanaan extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.sumber_pendanaan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_periode_pendanaan',
        'nama_sumber_pendanaan',
        'id_unit_kerja',
        'kategori_sumber_pendanaan',
        'total_pendanaan',
        'mata_uang',
        'maksimal_toleransi_similarity',
        'maksimal_toleransi_ai',
        'sisa_anggaran',
    ];

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'nama_sumber_pendanaan asc';
    const OPTION_COLUMN = 'nama_sumber_pendanaan';

    /**
     * Daftar kategori sumber pendanaan.
     */
    const CATEGORY_INTERNAL = 'internal';
    const CATEGORY_EXTERNAL = 'external';
    const CATEGORIES = [
        self::CATEGORY_INTERNAL => 'Internal Kampus',
        self::CATEGORY_EXTERNAL => 'Eksternal Kampus',
    ];

    /**
     * Daftar mata uang yang digunakan.
     */
    const CURRENCY_IDR = 'IDR';
    const CURRENCY_USD = 'USD';
    const LIST_CURRENCY = [
        self::CURRENCY_IDR => 'IDR',
        self::CURRENCY_USD => 'USD',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_periode_pendanaan' => ['required' => true, 'options' => PeriodePendanaan::class, 'option_value' => 'id', 'option_label' => 'tahun'],        // Periode
        'nama_sumber_pendanaan' => ['required' => true, 'maxlength' => 255],                                                                            // Nama Sumber Pendanaan
        'id_unit_kerja' => ['required' => true, 'options' => UnitKerja::class, 'option_value' => 'id', 'option_label' => 'name'],                       // Pengelola Bantuan
        'kategori_sumber_pendanaan' => ['required' => true, 'options' => self::CATEGORIES],                                                             // Kategori Sumber Pendanaan, Enum [Internal, Eksternal]
        'total_pendanaan' => ['required' => true, 'min' => 0, 'type' => 'numeric', 'control' => 'currency'],
        'mata_uang' => ['required' => true, 'maxlength' => 3, 'options' => self::LIST_CURRENCY],                                                        // Mata Uang
        'maksimal_toleransi_similarity' => ['required' => true, 'type' => 'numeric', 'min' => 0, 'max' => 100],                                         // Maksimal Nilai Similarity
        'maksimal_toleransi_ai' => ['required' => true, 'type' => 'numeric', 'min' => 0, 'max' => 100],                                                 // Maksimal Nilai AI

        // temporary field utk handle create/update mappingan dengan agenda kegiatan
        'optional_agenda_ids' => ['options' => AgendaKegiatan::class]
    ];

    protected static function newFactory()
    {
        return SumberPendanaanFactory::new();
    }

    public static function sumberPendanaanOptions(int $idPeriodePendanaan = null, array $excludeIdSumberPendanaan = null)
    {
        $idPeriodePendanaan ??= PeriodePendanaan::periodeAktif()?->id;

        return static::where('id_periode_pendanaan', $idPeriodePendanaan)
            ->when($excludeIdSumberPendanaan, fn($query, $excludeIdSumberPendanaan) => $query->whereNotIn('id', $excludeIdSumberPendanaan))
            ->orderByRaw(static::OPTION_ORDER)
            ->get(['id', static::OPTION_COLUMN])
            ->pluck(static::OPTION_COLUMN, 'id')
            ->toArray();
    }

    /**
     * Relasi ke master Tahapan Kegiatan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function agendaKegiatan()
    {
        return $this->belongsToMany(
            AgendaKegiatan::class,
            'litabmas.sumber_pendanaan_agenda_kegiatan',
            'id_sumber_pendanaan',
            'id_agenda_kegiatan'
        );
    }

    /**
     * Relasi ke klaster.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function klasterPendanaan(){
    return $this->hasMany(KlasterPendanaan::class, 'id_sumber_pendanaan');
    }

    /**
     * Relasei ke mappingan antara sumber pendanaan dengan agenda kegiatan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pivotAgendaKegiatan()
    {
        return $this->hasMany(SumberPendanaanAgendaKegiatan::class, 'id_sumber_pendanaan');
    }

    /**
     * Scope untuk periode aktif.
     *
     * @param $query
     * @return mixed
     */
    public function scopePeriodeAktif($query)
    {
        return $query->where('id_periode_pendanaan', PeriodePendanaan::periodeAktif()?->id);
    }

    public function periodePendanaan()
    {
        return $this->belongsTo(PeriodePendanaan::class, 'id_periode_pendanaan');
    }

    /**
     * Get sumber pendanaan berdasarkan beberapa parameter.
     * Digunakan hanya untuk select option yg berupa key (id) dan value (nama_sumber_pendanaan).
     *
     * @param int|null $idBidangIlmu
     * @param int|null $idTemaKegiatan
     * @param int|null $idKlasterPendanaan
     * @param string|null $kodeJenisPendanaan
     * @param int|null $idPeriodePendanaan
     * @return array|null
     */
    public static function optionDynamic(
        int $idBidangIlmu = null,
        int $idTemaKegiatan = null,
        int $idKlasterPendanaan = null,
        string $kodeJenisPendanaan = null,
        int $idPeriodePendanaan = null,
        int $idAgendaKegiatanPendaftaran = null,
    ) {
        return static::join('litabmas.klaster_pendanaan as fc', 'fc.id_sumber_pendanaan', '=', 'litabmas.sumber_pendanaan.id')
            ->join('litabmas.klaster_pendanaan_agenda_kegiatan as kpak', 'kpak.id_klaster_pendanaan', '=', 'fc.id')
            ->leftJoin('litabmas.klaster_pendanaan_bidang_ilmu as fcm', 'fcm.id_klaster_pendanaan', '=', 'fc.id')
            ->leftJoin('litabmas.klaster_pendanaan_bidang_ilmu_tema as fcmt', 'fcmt.id_klaster_pendanaan_bidang_ilmu', '=', 'fcm.id')
            ->select('litabmas.sumber_pendanaan.id', 'litabmas.sumber_pendanaan.nama_sumber_pendanaan')
            ->when($idPeriodePendanaan, function ($query, $idPeriodePendanaan) { // jika ada maka gunakan idPeriodePendanaan
                return $query->where('litabmas.sumber_pendanaan.id_periode_pendanaan', $idPeriodePendanaan);
            }, function ($query) { // jika tidak ada maka gunakan scopePeriodeAktif
                return $query->periodeAktif();
            })
            ->when($idBidangIlmu, fn($query) => $query->where('fcm.id_bidang_ilmu', $idBidangIlmu))
            ->when($idTemaKegiatan, fn($query) => $query->where('fcmt.id_tema_kegiatan', $idTemaKegiatan))
            ->when($idKlasterPendanaan, fn($query) => $query->where('fc.id', $idKlasterPendanaan))
            ->when($kodeJenisPendanaan, fn($query) => $query->where('fc.kode_jenis_pendanaan', $kodeJenisPendanaan))
            ->when($idAgendaKegiatanPendaftaran, function ($query, $idAgendaKegiatanPendaftaran) {
                return $query->where(function($query) use ($idAgendaKegiatanPendaftaran) {
                    return $query->where('kpak.id_agenda_kegiatan', $idAgendaKegiatanPendaftaran)
                        ->whereDate('kpak.waktu_mulai', '<=', now()->toDateString())
                        ->whereDate('kpak.waktu_selesai', '>=', now()->toDateString());
                });
            })
            ->orderBy('litabmas.sumber_pendanaan.nama_sumber_pendanaan', 'asc')
            ->pluck('litabmas.sumber_pendanaan.nama_sumber_pendanaan', 'litabmas.sumber_pendanaan.id')
            ->toArray();
    }
}
