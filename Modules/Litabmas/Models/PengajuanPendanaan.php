<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Biodata;
use Modules\Gate\Models\Role;
use Modules\Gate\Models\User;
use Modules\Litabmas\Database\factories\PengajuanPendanaanFactory;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Enums\StatusAgendaKegiatanEnum;

class PengajuanPendanaan extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'judul_penelitian',
        'kode_registrasi',
        'apakah_berkontribusi_bidang_ilmu',
        'id_dokumen_proposal',
        'waktu_snk_disetujui',
        'mata_uang',
        'nominal_anggaran_diajukan',
        'nominal_anggaran_disetujui',
        'nominal_anggaran_terpakai',
        'persentase_anggaran_dicairkan',
        'id_dokumen_rab',
        'nama_pemilik_rekening',
        'nomor_rekening',
        'nama_bank',
        'cabang_bank',
        'id_foto_tabungan',
        'status_agenda_kegiatan',
        'status_similarity',
        'kode_jenis_pendanaan',
        'id_sumber_pendanaan',
        'id_klaster_pendanaan',
        'id_bidang_ilmu',
        'id_tema_kegiatan',
        'apakah_lolos_nominasi',
        'waktu_lolos_nominasi',
        'lolos_nominasi_oleh',
        'apakah_lolos_pendanaan',
        'waktu_lolos_pendanaan',
        'lolos_pendanaan_oleh',
        'penilaian_index_similarity',
        'id_dokumen_penilaian_similarity',
        'penilaian_index_ai',
        'id_dokumen_penilaian_ai',
        'total_nilai_komposisi_proposal',
        'total_nilai_presentasi_proposal',
        'kesimpulan_output_bersama',
        'apakah_afirmasi',
        'catatan_lolos_pendanaan',
        'id_biodata_penyetuju_biaya',
        'waktu_setuju_biaya',
        'id_dokumen_sk_peneliti',
    ];

    protected $casts = [
        'waktu_snk_disetujui' => 'datetime',
        'waktu_lolos_nominasi' => 'datetime',
        'waktu_lolos_pendanaan' => 'datetime',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'judul_penelitian' => ['required' => true, 'maxlength' => 255],                                      // Judul
        'kode_registrasi' => ['maxlength' => 255, 'unique' => true],                                         // ID Registrasi
        // 'apakah_berkontribusi_bidang_ilmu' => ['required' => true, 'type' => 'boolean'],                     // Apakah penelitian ini berkontribusi pada pengembangan keilmuan di Prodi?
        'id_dokumen_proposal' => ['file_type' => self::FILE_TYPE_PROPOSAL, 'max_size' => self::MAX_SIZE_FILE_PROPOSAL],                           // File Proposal
        'waktu_snk_disetujui' => ['type' => 'timestamp'],                                                    // Waktu menyetujui Syarat dan Ketentuan

        // terkait perduitan
        'mata_uang' => ['required' => true, 'maxlength' => 3, 'options' => SumberPendanaan::LIST_CURRENCY],  // Mata Uang
        'nominal_anggaran_diajukan' => ['type' => 'numeric', 'control' => 'currency'],                                                // Usulan Biaya
        'nominal_anggaran_disetujui' => ['type' => 'numeric', 'control' => 'currency'],                                               // Biaya Disetujui
        'nominal_anggaran_terpakai' => ['type' => 'numeric', 'control' => 'currency'],                                                // Biaya Terpakai
        'persentase_anggaran_dicairkan' => ['type' => 'numeric'],                                            // Status Biaya Dicairkan
        'id_dokumen_rab' => ['max_size' => 1024 * 5, 'file_type' => ['pdf']],                                // File Rancangan Anggaran Biaya
        'nama_pemilik_rekening' => ['maxlength' => 100],                                                     // Nama Pemilik Rekening
        'nomor_rekening' => ['maxlength' => 30],                                                             // Nomor Rekening
        'nama_bank' => ['maxlength' => 30],                                                                  // Nama Bank
        'cabang_bank' => ['maxlength' => 30],                                                                // Cabang Bank
        'id_foto_tabungan' => ['max_size' => 1024 * 2, 'file_type' => ['jpg', 'jpeg', 'png']],               // Foto Halaman Buku Tabungan
        'status_agenda_kegiatan' => ['required' => true],   // Status Tahapan Kegiatan

        // terkait relasinya
        'kode_jenis_pendanaan' => ['required' => true, 'options' => JenisPendanaanEnum::CODES],              // Jenis Pendanaan
        'id_sumber_pendanaan' => ['required' => true, 'options' => SumberPendanaan::class],                  // Sumber Pendanaan
        'id_klaster_pendanaan' => ['required' => true, 'options' => KlasterPendanaan::class],                // Klaster Pendanaan
        'id_bidang_ilmu' => ['required' => true, 'options' => BidangIlmu::class],                            // Bidang Ilmu
        'id_tema_kegiatan' => ['required' => true, 'options' => TemaKegiatan::class],                        // Tema Kegiatan

        // validasi lolos nominasi
        'apakah_lolos_nominasi' => ['type' => 'boolean'],                                                    // Apakah lolos nominasi?
        'waktu_lolos_nominasi' => ['type' => 'timestamp'],                                                   // Waktu lolos nominasi
        'lolos_nominasi_oleh' => ['type' => 'integer', 'options' => User::class],                            // Divalidasi oleh

        // validasi lolos pendanaan
        'apakah_lolos_pendanaan' => ['type' => 'boolean'],                                                   // Apakah lolos pendanaan?
        'waktu_lolos_pendanaan' => ['type' => 'timestamp'],                                                  // Waktu lolos pendanaan
        'lolos_pendanaan_oleh' => ['type' => 'integer', 'options' => User::class],                           // Divalidasi oleh

        // similarity dan ai
        'penilaian_index_similarity' => ['type' => 'numeric'],                                               // Hasil Checking Similarity
        'id_dokumen_penilaian_similarity' => [
            'file_type' => ['pdf'],
            'max_size' => (1024 * 5)
        ],                                                                       // File Bukti Hasil Similarity
        'penilaian_index_ai' => ['type' => 'numeric'],                                                       // Hascil Checking Artificial Intelligence
        'id_dokumen_penilaian_ai' => [
            'file_type' => ['pdf'],
            'max_size' => (1024 * 5)
        ],                                                                       // File Bukti Hasil Artificial Intelligence

        // biar nggk berat harus selalu ngitung total skor dari sclae/skala, maka disimpan aja di proposalnya
        'total_nilai_komposisi_proposal' => ['type' => 'numeric'],                                       // Total Nilai Keseluruhan Aspek Komposisi Proposal
        'total_nilai_presentasi_proposal' => ['type' => 'numeric'],                                      // Total Nilai Keseluruhan Aspek Presentasi Proposal

        'kesimpulan_output_bersama' => [],
        'apakah_afirmasi' => ['type' => 'boolean'],
        'catatan_lolos_pendanaan' => ['maxlength' => 255],

        // terkait penyetujuan biaya
        'id_biodata_penyetuju_biaya' => ['type' => 'integer', 'options' => Biodata::class],                    // Penyetuju Biaya
        'waktu_setuju_biaya' => ['type' => 'timestamp'],

        // sk peneliti
        'id_dokumen_sk_peneliti' => ['file_type' => ['pdf'], 'max_size' => 1024 * 5],                           // File SK Peneliti
    ];

    /**
     * Konstanta utk id_dokumen_proposal
     */
    const MAX_SIZE_FILE_PROPOSAL = 1024 * 2;
    const FILE_TYPE_PROPOSAL = ['pdf'];

    const LOLOS_SIMILARITY_AI = 'lolos';
    const TIDAK_LOLOS_SIMILARITY_AI = 'tidak_lolos';

    /**
     * Konstanta utk id_dokumen_proposal
     */
    const MAX_SIZE_FILE_DOKUMEN_SK_PENELITI = 1024 * 2;
    const FILE_TYPE_DOKUMEN_SK_PENELITI = ['pdf'];
    /**
     * Utk validasi unique composite.
     * @return array
     */
    protected static function uniqueColumns(): array
    {
        return [
            'firstUniqueCode' => [
                'defaultMessage' => 'Gagal menyimpan karena duplikasi data. Jenis Pendanaan, Sumber Pendanaan, Klaster Pendanaan, Tema Kegiatan, Bidang Ilmu, dan Judul Proposal sudah ada.',
                'fields' => [
                    'kode_jenis_pendanaan',
                    'id_sumber_pendanaan',
                    'id_klaster_pendanaan',
                    'id_tema_kegiatan',
                    'id_bidang_ilmu',
                    'judul_penelitian'
                ]
            ]
        ];
    }

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'judul_penelitian asc';
    const OPTION_COLUMN = 'judul_penelitian';

    /**
     * Display options.
     * @return array
     */
    public static function options(int $idBiodata = null, string $kodeRole = null)
    {
        $idBiodata ??= auth()->user()?->biodata?->id;
        $kodeRole ??= auth()->user()?->kode_role;
        $roleDosen = [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL];

        return static::when(in_array($kodeRole, $roleDosen), function ($query) use ($idBiodata) {
            $query->join('litabmas.pengajuan_pendanaan_anggota as ppa', 'ppa.id_pengajuan_pendanaan', '=', 'litabmas.pengajuan_pendanaan.id')
                ->where('ppa.id_biodata', $idBiodata);
        })
            ->orderByRaw(static::OPTION_ORDER)
            ->get(['litabmas.pengajuan_pendanaan.id', static::OPTION_COLUMN])
            ->pluck(static::OPTION_COLUMN, 'id')
            ->toArray();
    }

    protected static function newFactory()
    {
        return PengajuanPendanaanFactory::class;
    }

    /**
     * Relasi ke sumber pendanaan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sumberPendanaan()
    {
        return $this->belongsTo(SumberPendanaan::class, 'id_sumber_pendanaan');
    }

    /**
     * Relasi ke klaster pendanaan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function klasterPendanaan()
    {
        return $this->belongsTo(KlasterPendanaan::class, 'id_klaster_pendanaan');
    }

    /**
     * Relasi ke status pengajuan pendanaan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function statusPengajuanPendanaan()
    {
        return $this->belongsTo(PengajuanPendanaanStatus::class, 'id', 'id_pengajuan_pendanaan');
    }

    /**
     * Relasi ke anggota pengajuan pendanaan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function anggota()
    {
        return $this->hasMany(PengajuanPendanaanAnggota::class, 'id_pengajuan_pendanaan', 'id');
    }

    /**
     * Get value status penilaian administrasi.
     *
     * @return Attribute
     */
    public function statusPenilaianAdministrasi(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->statusPengajuanPendanaan->status_penilaian_administrasi ?? null;
            }
        );
    }
}
