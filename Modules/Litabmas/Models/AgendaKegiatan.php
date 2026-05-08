<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Traits\ClearCache;
use Modules\Litabmas\Database\factories\AgendaKegiatanFactory;
use Modules\Litabmas\Models\Cache\AgendaKegiatanCache;

class AgendaKegiatan extends IndonesianModel
{
    use SoftDeletes, HasFactory, ClearCache;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.agenda_kegiatan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_agenda',
        'nama_agenda',
        'apakah_wajib',
        'urutan'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_agenda' => ['required' => true, 'maxlength' => 100, 'unique' => true], // Kode Tahapan Kegiatan
        'nama_agenda' => ['required' => true, 'maxlength' => 255, 'unique' => true], // Tahapan Kegiatan
        'apakah_wajib' => ['required' => true, 'type' => 'boolean'], // Tahapan Kegiatan Wajib
        'urutan' => ['type' => 'integer'] // Urutan Kegiatan
    ];

    /**
     * Konstanta untuk setiap agenda kegiatan.
     * Setiap agenda memiliki flow/alur tersendiri di LITABMAS.
     * Jadi tidak bisa ditambahkan, diubah, atau dihapus melalui web.
     */
    const STEP_PENDAFTARAN = 'pendaftaran';
    const STEP_SELEKSI_ADMINISTRASI = 'seleksi_administrasi';
    const STEP_PENGUMUMAN_ADMINISTRASI = 'pengumuman_administrasi';
    const STEP_FEEDBACK_REVIEWER = 'feedback_reviewer';
    const STEP_PENENTUAN_NOMINASI = 'penentuan_nominasi';
    const STEP_PENGUMUMAN_NOMINASI = 'pengumuman_nominasi';
    const STEP_PENILAIAN_HASIL_PRESENTASI = 'penilaian_hasil_presentasi';
    const STEP_PENENTUAN_PENDANAAN = 'penentuan_pendanaan';
    const STEP_PENGUMUMAN_PENDANAAN = 'pengumuman_pendanaan';
    const STEP_PENINJAUAN_LOGBOOK = 'peninjauan_logbook';
    const STEP_PENILAIAN_LAPORAN_ANTARA = 'penilaian_laporan_antara';
    const STEP_PENILAIAN_LUARAN = 'penilaian_luaran';
    const STEP_PENGUMPULAN_HASIL = 'pengumpulan_hasil';

    // step yang wajib ada untuk spesifik agenda
    const REQUIRED_AGENDA_MAPPING = [
        // self::STEP_FEEDBACK_REVIEWER => [self::STEP_PENILAIAN_HASIL_PRESENTASI],
        self::STEP_PENILAIAN_HASIL_PRESENTASI => [self::STEP_FEEDBACK_REVIEWER],
        self::STEP_PENILAIAN_LAPORAN_ANTARA => [self::STEP_FEEDBACK_REVIEWER],
        self::STEP_PENGUMUMAN_NOMINASI => [self::STEP_PENENTUAN_NOMINASI],
    ];
    const VALIDATION_REQUIRED_MAPPING = [
        self::STEP_FEEDBACK_REVIEWER => [
            self::STEP_PENILAIAN_HASIL_PRESENTASI,
            self::STEP_PENILAIAN_LAPORAN_ANTARA
        ],
        // self::STEP_PENILAIAN_HASIL_PRESENTASI => [
        //     self::STEP_FEEDBACK_REVIEWER,
        // ],
        self::STEP_PENENTUAN_NOMINASI => [
            self::STEP_PENGUMUMAN_NOMINASI,
        ]
    ];

    // step yang hanya satu hari dan hanya di awal
    const ONLY_FIRST_DAY_STEPS = [
        self::STEP_PENGUMUMAN_ADMINISTRASI,
        self::STEP_PENGUMUMAN_NOMINASI,
        self::STEP_PENGUMUMAN_PENDANAAN,
    ];

    // step yang hanya satu hari dan hanya di akhir
    const ONLY_LAST_DAY_STEPS = [
        self::STEP_PENGUMPULAN_HASIL,
    ];

    // step yang tidak boleh berpotongan dengan step lain
    const ALLOWED_INTERSECT_STEPS = [
        self::STEP_PENINJAUAN_LOGBOOK,
        self::STEP_PENILAIAN_LAPORAN_ANTARA,
        self::STEP_PENILAIAN_LUARAN,
    ];

    const LIST_STEP_PENDAFTARAN = [
        PengajuanPendanaanStatus2::LEVEL1_DRAFT => true,
        PengajuanPendanaanStatus2::LEVEL2_KONFIRMASI_ANGGOTA => false,
        PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN => true,
    ];

    const LIST_STEP_SELEKSI_ADMINISTRASI = [
        PengajuanPendanaanStatus2::LEVEL5_PROSES_SELEKSI_ADMINISTRASI => true,
        PengajuanPendanaanStatus2::LEVEL5_TIDAK_LOLOS_ADMINISTRASI => true,
        PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI => true,
    ];

    const LIST_STEP_PENGUMUMAN_ADMINISTRASI = [];

    const LIST_STEP_FEEDBACK_REVIEWER = [
        PengajuanPendanaanStatus2::LEVEL7_PENINJAUAN_PROPOSAL => true,
    ];

    const LIST_STEP_PENENTUAN_NOMINASI = [
        PengajuanPendanaanStatus2::LEVEL8_NOMINASI_BELUM_DITINJAU => true,
        PengajuanPendanaanStatus2::LEVEL8_TIDAK_LOLOS_NOMINASI => true,
        PengajuanPendanaanStatus2::LEVEL8_LOLOS_NOMINASI => true,
    ];

    const LIST_STEP_PENGUMUMAN_NOMINASI = [];

    const LIST_STEP_PENILAIAN_HASIL_PRESENTASI = [];

    const LIST_STEP_PENENTUAN_PENDANAAN = [
        PengajuanPendanaanStatus2::LEVEL10_BELUM_PENENTUAN_PENDANAAN => true,
        PengajuanPendanaanStatus2::LEVEL10_TIDAK_LOLOS_PENDANAAN => true,
        PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN => true,
    ];

    const LIST_STEP_PENGUMUMAN_PENDANAAN = [];

    const LIST_STEP_PENINJAUAN_LOGBOOK = [];

    const LIST_STEP_PENILAIAN_LAPORAN_ANTARA = [];

    const LIST_STEP_PENILAIAN_LUARAN = [];

    const LIST_STEP_PENGUMPULAN_HASIL = [
        PengajuanPendanaanStatus2::LEVEL12_PENGUMPULAN_HASIL => true,
    ];

    public function getListStepAgenda()
    {
        $const = strtoupper('list_step_' . $this->kode_agenda);

        if (defined('static::' . $const)) {
            return constant('static::' . $const);
        }
    }

    /**
     * Get list data agenda kegiatan dari database (cached).
     *
     * @return Collection
     */
    public function getListCache()
    {
        // AgendaKegiatanCache::destroy();
        return AgendaKegiatanCache::get();
    }

    /**
     * Clear/forgot cache.
     */
    private static function clearCache($model)
    {
        AgendaKegiatanCache::destroy();
    }

    //relasi ke klaster pendanaan many to many using pivot table klaster_pendanaan_agenda_kegiatan
    public function klasterPendanaan()
    {
        return $this->belongsToMany(KlasterPendanaan::class, 'litabmas.klaster_pendanaan_agenda_kegiatan', 'id_agenda_kegiatan', 'id_klaster_pendanaan');
    }
}
