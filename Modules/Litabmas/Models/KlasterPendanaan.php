<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Gate\Models\Role;
use Modules\Litabmas\Database\factories\KlasterPendanaanFactory;
use Modules\Litabmas\Enums\JenisPendanaanEnum;

class KlasterPendanaan extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.klaster_pendanaan';

    const OPTION_COLUMN = 'nama_klaster';
    const OPTION_ORDER = 'nama_klaster asc';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_jenis_pendanaan',
        'id_sumber_pendanaan',
        'nama_klaster',
        'persyaratan_administratif_ketua',
        'apakah_butuh_approve_semua_anggota',
        'kategori_klaster',
        'minimal_anggota',
        'maksimal_anggota',
        'maksimal_anggaran',
        'mata_uang',
        'apakah_sudah_publikasi',
        'id_dokumen_template_rab',
        'apakah_bisa_multi_ajuan',
        'maksimal_ajuan_per_user'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_jenis_pendanaan' => ['required' => true, 'options' => JenisPendanaanEnum::CODES],                         // Jenis Pendanaan
        'id_sumber_pendanaan' => ['required' => true, 'options' => SumberPendanaan::class],                             // Sumber Pendanaan
        'nama_klaster' => ['required' => true, 'maxlength' => 255],                                                     // Nama Klaster Pendanaan
        'persyaratan_administratif_ketua' => ['required' => false, 'type' => 'json'],                                   // Persyaratan Administratif Ketua
        'apakah_butuh_approve_semua_anggota' => ['required' => true, 'type' => 'boolean'],                              // Untuk mengajukan proposal apakah perlu approval semua anggota?
        'kategori_klaster' => ['options' => self::KATEGORI_OPTIONS],                                                    // Kategori Klaster
        'minimal_anggota' => ['type' => 'numeric', 'min' => 1, 'max' => 10],                                             // Minimal Anggota
        'maksimal_anggota' => ['type' => 'numeric', 'min' => 1, 'max' => 10],                                            // Maksimal Anggota
        'maksimal_anggaran' => ['required' => true, 'type' => 'numeric', 'min' => 1, 'control' => 'currency'],          // Maksimal Anggaran
        'mata_uang' => ['required' => true, 'maxlength' => 3, 'options' => SumberPendanaan::LIST_CURRENCY],             // Mata Uang
        'apakah_sudah_publikasi' => ['type' => 'boolean'],                                                              // Apakah klaster sudah dipublikasi? & INI JUGA FLAGGING BUAT DRAFT
        'id_dokumen_template_rab' => ['file_type' => ['xls', 'xlsx'], 'max_size' => (1024 * 2)],                         // Dokumen Template RAB
        'apakah_bisa_multi_ajuan' => ['type' => 'boolean'],                                                             // Apakah klaster ini memungkinkan pengajuan lebih dari 1x per user
        'maksimal_ajuan_per_user' => ['type' => 'numeric', 'min' => 1, 'max' => 10],                                       // Maksimal ajuan pada klaster ini
    ];

    /**
     * Kategori Klaster Pendanaan.
     */
    const KATEGORI_INDIVIDU = 'individu';
    const KATEGORI_KELOMPOK = 'kelompok';
    const KATEGORI_OPTIONS = [
        self::KATEGORI_INDIVIDU => 'Individu',
        self::KATEGORI_KELOMPOK => 'Kelompok',
    ];

    const PUBLIKASI_OPTIONS = [
        false => 'Belum Dipublikasi',
        true => 'Sudah Dipublikasi',
    ];
    const PUBLIKASI_BADGE = [
        false => 'default',
        true => 'success',
    ];

    /**
     * Multi Ajuan Options.
     */
    const MULTI_AJUAN_OPTIONS = [
        false => 'Tidak Bisa Multi Ajuan (1x per user)',
        true => 'Bisa Multi Ajuan (Lebih dari 1x per user)',
    ];

    /**
     * Maksimal Ajuan Per User Options.
     */
    const MAX_SUBMISSION_OPTIONS = [
        1 => '1 Pengajuan Proposal',
        2 => '2 Pengajuan Proposal',
        3 => '3 Pengajuan Proposal',
        4 => '4 Pengajuan Proposal',
        5 => '5 Pengajuan Proposal',
        6 => '6 Pengajuan Proposal',
        7 => '7 Pengajuan Proposal',
        8 => '8 Pengajuan Proposal',
        9 => '9 Pengajuan Proposal',
        10 => '10 Pengajuan Proposal',
    ];

    /**
     * Accessor untuk mendapatkan maksimal ajuan secara dinamis.
     */
    public function getMaksimalAjuanPerUserAttribute($value)
    {
        // Jika klaster diatur tidak bisa multi ajuan, paksa kembalikan angka 1
        if (!$this->apakah_bisa_multi_ajuan) {
            return 1;
        }

        // Jika bisa multi ajuan dan nilai di DB adalah NULL
        if (is_null($value)) {
            // Ambil nilai terbaru dari Periode Pendanaan melalui relasi Sumber Pendanaan
            return $this->sumberPendanaan?->periodePendanaan?->maksimal_ketua_mendaftar ?? 1;
        }

        return $value;
    }

    /**
     * Utk validasi unique composite.
     * @return array[]
     */
    protected static function uniqueColumns(): array
    {
        return [
            'uniqueNamaKlaster' => [
                'defaultMessage' => 'Gagal menyimpan karena duplikasi data.
                    Sumber Pendanaan dan Nama Klaster Pendanaan tersebut sudah ada sebelumnya.',
                'fields' => ['id_sumber_pendanaan', 'nama_klaster']
            ],
        ];
    }

    /**
     * @return KlasterPendanaanFactory
     */
    protected static function newFactory()
    {
        return KlasterPendanaanFactory::new();
    }

    /**
     * Display options.
     * @return array
     */
    public static function options(int $idSumberPendanaan = null)
    {
        $orderColumn = static::OPTION_ORDER;
        $userRole = auth()->user()?->kode_role;
        $roleMustCheckScope = [Role::ROLE_DEKAN];
        $userUnitKerja = session()->get('user.unit_kerja');

        return static::orderByRaw($orderColumn)
            // cek id sumber pendanaan jika ada
            ->when($idSumberPendanaan, fn($query) => $query->where('klaster_pendanaan.id_sumber_pendanaan', $idSumberPendanaan))
            // cek scope user jika dekan
            ->when(in_array($userRole, $roleMustCheckScope) && !empty($userUnitKerja), function ($query) use ($userUnitKerja) {
                $query->join('litabmas.sumber_pendanaan as sp', 'sp.id', '=', 'litabmas.klaster_pendanaan.id_sumber_pendanaan')
                    ->join('core.unit_kerja as uk', 'uk.id', '=', 'sp.id_unit_kerja')
                    ->where('uk.apakah_aktif', true)
                    ->where(function ($query) use ($userUnitKerja) {
                        $query->where('uk.info_left', '>=', $userUnitKerja->info_left)
                            ->where('uk.info_right', '<=', $userUnitKerja->info_right)
                            ->orWhere('uk.id', $userUnitKerja->id); // klo pengelola univ maka semua fakultas bisa lihat
                    });
            })
            ->get(['litabmas.klaster_pendanaan.id', static::OPTION_COLUMN])
            ->pluck(static::OPTION_COLUMN, 'id')
            ->toArray();
    }

    /**
     * Relasi ke sumber pendanaan.
     *
     * @return BelongsTo
     */
    public function sumberPendanaan()
    {
        return $this->belongsTo(SumberPendanaan::class);
    }

    /**
     * Relasi ke master pengajuan pendanaan.
     *
     * @return HasMany
     */
    public function pengajuanPendanaan()
    {
        return $this->hasMany(PengajuanPendanaan::class, 'id_klaster_pendanaan');
    }

    /**
     * Relasi ke master bidang ilmu.
     *
     * @return BelongsToMany
     */
    public function bidangIlmu()
    {
        return $this->belongsToMany(
            BidangIlmu::class,
            'litabmas.klaster_pendanaan_bidang_ilmu',
            'id_klaster_pendanaan',
            'id_bidang_ilmu'
        )->where('litabmas.klaster_pendanaan_bidang_ilmu.waktu_dihapus', null)
            ->withPivot('id');
    }

    /**
     * Relasi ke mappingan bidang ilmu.
     *
     * @return HasMany
     */
    public function pivotBidangIlmu()
    {
        return $this->hasMany(KlasterPendanaanBidangIlmu::class, 'id_klaster_pendanaan');
    }

    /**
     * Relasi ke master output.
     *
     * @return BelongsToMany
     */
    public function jenisOutputPenelitian()
    {
        return $this->belongsToMany(
            JenisOutputPenelitian::class,
            'litabmas.klaster_pendanaan_output_penelitian',
            'id_klaster_pendanaan',
            'id_jenis_output_penelitian'
        )->withPivot('apakah_wajib')
            ->where('litabmas.klaster_pendanaan_output_penelitian.waktu_dihapus', null);
    }

    /**
     * Relasi ke table mappingan output.
     *
     * @return HasMany
     */
    public function pivotJenisOutputPenelitian()
    {
        return $this->hasMany(KlasterPendanaanOutputPenelitian::class, 'id_klaster_pendanaan');
    }

    /**
     * Relasi ke master outcome.
     *
     * @return BelongsToMany
     */
    public function jenisOutcomePenelitian()
    {
        return $this->belongsToMany(
            JenisOutcomePenelitian::class,
            'litabmas.klaster_pendanaan_outcome_penelitian',
            'id_klaster_pendanaan',
            'id_jenis_outcome_penelitian'
        )->withPivot('apakah_wajib')
            ->where('litabmas.klaster_pendanaan_outcome_penelitian.waktu_dihapus', null);
    }

    /**
     * Relasi ke table mappingan outcome.
     *
     * @return HasMany
     */
    public function pivotJenisOutcomePenelitian()
    {
        return $this->hasMany(KlasterPendanaanOutcomePenelitian::class, 'id_klaster_pendanaan');
    }

    /**
     * Relasi ke agenda kegiatan
     *
     * @return BelongsToMany
     */
    public function agendaKegiatan()
    {
        return $this->belongsToMany(
            AgendaKegiatan::class,
            'litabmas.klaster_pendanaan_agenda_kegiatan',
            'id_klaster_pendanaan',
            'id_agenda_kegiatan'
        )->withPivot('waktu_mulai', 'waktu_selesai')
            ->where('litabmas.klaster_pendanaan_agenda_kegiatan.waktu_dihapus', null);
    }

    /**
     * Relasi ke mappingan agenda kegiatan
     *
     * @return HasMany
     */
    public function pivotAgendaKegiatan()
    {
        return $this->hasMany(KlasterPendanaanAgendaKegiatan::class, 'id_klaster_pendanaan');
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

    /**
     * Get klaster pendanaan berdasarkan periode aktif.
     *
     * @return array|null
     */
    public static function getKlasterPendanaanAktifOptions(int $idPeriodePendanaan = null)
    {
        // get berdasarkan periode aktif
        $idPeriodePendanaan ??= PeriodePendanaan::periodeAktif()?->id;

        return static::join('litabmas.sumber_pendanaan as fs', 'fs.id', '=', 'litabmas.klaster_pendanaan.id_sumber_pendanaan')
            ->where('fs.id_periode_pendanaan', $idPeriodePendanaan)
            ->orderByRaw(static::OPTION_ORDER)
            ->get(['litabmas.klaster_pendanaan.id', 'litabmas.klaster_pendanaan.nama_klaster'])
            ->pluck(static::OPTION_COLUMN, 'id')
            ->toArray();
    }

    /**
     * Get options maksimal anggota
     *
     * @return string[]
     */
    public static function maxMemberOptions()
    {
        return [
            1 => '1 Anggota',
            2 => '2 Anggota',
            3 => '3 Anggota',
            4 => '4 Anggota',
            5 => '5 Anggota',
            6 => '6 Anggota',
            7 => '7 Anggota',
            8 => '8 Anggota',
            9 => '9 Anggota',
            10 => '10 Anggota',
        ];
    }

    /**
     * Get options minimal anggota
     *
     * @return string[]
     */
    public static function minMemberOptions()
    {
        return [
            1 => '1 Anggota',
            2 => '2 Anggota',
            3 => '3 Anggota',
            4 => '4 Anggota',
            5 => '5 Anggota',
            6 => '6 Anggota',
            7 => '7 Anggota',
            8 => '8 Anggota',
            9 => '9 Anggota',
            10 => '10 Anggota',
        ];
    }

    /**
     * Get options maksimal pengajuan per user
     *
     * @return string[]
     */
    public static function maxSubmissionOptions()
    {
        return self::MAX_SUBMISSION_OPTIONS;
    }

    /**
     * Get klaster pendanaanberdasarkan beberapa parameter.
     * Digunakan hanya untuk select option yg berupa key (id) dan value (nama_klaster).
     *
     * @param int|null $idBidangIlmu
     * @param int|null $idTemaKegiatan
     * @param int|null $idSumberPendanaan
     * @param string|null $kodeJenisPendanaan
     * @param int|null $idPeriodePendanaan
     * @return array|null
     */
    public static function optionDynamic(
        int $idBidangIlmu = null,
        int $idTemaKegiatan = null,
        int $idSumberPendanaan = null,
        string $kodeJenisPendanaan = null,
        int $idPeriodePendanaan = null,
        int $idAgendaKegiatanPendaftaran = null,
    ) {
        return static::select('litabmas.klaster_pendanaan.id', 'litabmas.klaster_pendanaan.nama_klaster', 'kpak.waktu_selesai')
            ->join('litabmas.sumber_pendanaan as fs', 'fs.id', '=', 'litabmas.klaster_pendanaan.id_sumber_pendanaan')
            ->join('litabmas.klaster_pendanaan_agenda_kegiatan as kpak', 'kpak.id_klaster_pendanaan', '=', 'litabmas.klaster_pendanaan.id')
            ->leftJoin('litabmas.klaster_pendanaan_bidang_ilmu as fcm', 'fcm.id_klaster_pendanaan', '=', 'litabmas.klaster_pendanaan.id')
            ->leftJoin('litabmas.klaster_pendanaan_bidang_ilmu_tema as fcmt', 'fcmt.id_klaster_pendanaan_bidang_ilmu', '=', 'fcm.id')
            ->when($idPeriodePendanaan, function ($query, $idPeriodePendanaan) { // jika ada maka gunakan idPeriodePendanaan
                return $query->where('fs.id_periode_pendanaan', $idPeriodePendanaan);
            }, function ($query) { // jika tidak ada maka gunakan get periode aktif
                $fundingPeriod = PeriodePendanaan::periodeAktif();
                return $query->where('fs.id_periode_pendanaan', $fundingPeriod?->id);
            })
            ->when($idBidangIlmu, fn($query) => $query->where('fcm.id_bidang_ilmu', $idBidangIlmu))
            ->when($idTemaKegiatan, fn($query) => $query->where('fcmt.id_tema_kegiatan', $idTemaKegiatan))
            ->when($idSumberPendanaan, fn($query) => $query->where('fs.id', $idSumberPendanaan))
            ->when($kodeJenisPendanaan, fn($query) => $query->where('litabmas.klaster_pendanaan.kode_jenis_pendanaan', $kodeJenisPendanaan))
            // ->when($idAgendaKegiatanPendaftaran, function ($query, $idAgendaKegiatanPendaftaran) {
            //     return $query->where(function($query) use ($idAgendaKegiatanPendaftaran) {
            //         return $query->where('kpak.id_agenda_kegiatan', $idAgendaKegiatanPendaftaran)
            //             ->whereDate('kpak.waktu_mulai', '<=', now()->toDateString())
            //             ->whereDate('kpak.waktu_selesai', '>=', now()->toDateString());
            //     });
            // })
            ->orderBy('litabmas.klaster_pendanaan.nama_klaster', 'asc')
            ->pluck('litabmas.klaster_pendanaan.nama_klaster', 'litabmas.klaster_pendanaan.id')
            ->toArray();
    }
}
