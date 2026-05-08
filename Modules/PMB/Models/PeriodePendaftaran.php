<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\SistemKuliah;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\PeriodeAkademik;
use Modules\Core\Models\JenisPendaftaran;
use Modules\Core\Models\Traits\ClearCache;
use Modules\PMB\Models\Cache\PeriodePendaftaranCache;

class PeriodePendaftaran extends IndonesianModel
{
    use ClearCache, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.periode_pendaftaran';

    const OPTION_ORDER = 'nama_periode';

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_PUBLISHED => 'Published',
    ];

    const REPORT_EVALUATION_YES = 'yes';
    const REPORT_EVALUATION_NO = 'no';
    const REPORT_EVALUATION_OPTIONAL = 'optional';
    const REPORT_EVALUATIONS = [
        self::REPORT_EVALUATION_YES => 'Ya',
        self::REPORT_EVALUATION_NO => 'Tidak',
        self::REPORT_EVALUATION_OPTIONAL => 'Opsional',
    ];

    const QUALIFICATION_PROCESS_MANUAL = 'manual';
    const QUALIFICATION_PROCESS_AUTO_RECOMMENDED = 'auto-recommended';
    const QUALIFICATION_PROCESS_AUTO_QUALIFIED = 'auto-qualified';
    const QUALIFICATION_PROCESSES = [
        self::QUALIFICATION_PROCESS_MANUAL => 'Manual',
        self::QUALIFICATION_PROCESS_AUTO_RECOMMENDED => 'Auto Rekomendasi',
        self::QUALIFICATION_PROCESS_AUTO_QUALIFIED => 'Auto Lulus',
    ];

    /**
     * Display options for amount_of_program_options & amount_of_program_options_required.
     * @var array
     */
    const OPTION_AMOUNT_OF_PROGRAMS = [
        1 => 1,
        2 => 2,
        3 => 3,
    ];

    protected $fillable = [
        'kode_periode',
        'nama_periode',
        'id_periode_akademik',
        'id_gelombang',
        'id_jalur_pendaftaran',
        'id_sistem_kuliah',
        'id_jenis_pendaftaran',
        'keterangan_periode',
        'apakah_berbayar',
        'status_periode',
        'waktu_dibuka',
        'waktu_ditutup',
        'tahun_lulus_akhir',
        'tanggal_minimal_batas_lahir',
        'tanggal_maksimal_batas_lahir',
        'tanggal_awal_daftar_ulang',
        'tanggal_akhir_daftar_ulang',
        'waktu_pengumuman_kelulusan',
        'waktu_pengumuman_nilai',
        'apakah_tampilkan_daya_tampung',
        'apakah_tampilkan_nilai',
        'dapat_mengubah_prodi',
        'dapat_pilih_prodi_sama',
        'dapat_pilih_fakultas_sama',
        'keterangan_finalisasi',
        'waktu_akhir_finalisasi',
        'penilaian_rapor',
        'batas_tanggal_va',
        'proses_kelulusan',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_periode' => ['required' => true, 'unique' => true, 'maxlength' => 255], // Code Periode Pendaftaran
        'nama_periode' => ['required' => true, 'maxlength' => 255], // Nama Periode Pendaftaran
        'id_periode_akademik' => ['required' => true, 'options' => PeriodeAkademik::class], // Periode Akademik
        'id_gelombang' => ['required' => true, 'options' => Gelombang::class], // Gelombang
        'id_jalur_pendaftaran' => ['required' => true, 'options' => JalurPendaftaran::class], // Jalur Pendaftaran
        'id_sistem_kuliah' => ['required' => true, 'options' => SistemKuliah::class], // Sistem Kuliah
        'id_jenis_pendaftaran' => ['required' => true, 'options' => JenisPendaftaran::class], // Jenis Pendaftaran
        'keterangan_periode' => ['maxlength' => 255], // Deskripsi Periode Pendaftaran
        'apakah_berbayar' => ['type' => 'boolean'], // Periode Pendaftaran Berbayar?
        'status_periode' => ['required' => true, 'maxlength' => 50, 'options' => self::STATUSES], // Status Periode Pendaftaran (Draft, Published)
        'waktu_dibuka' => ['type'=> 'timestamp', 'validation' => 'date_format:Y-m-d\TH:i'], // Waktu Periode Pendaftaran Dibuka
        'waktu_ditutup' => ['type'=> 'timestamp', 'validation' => 'date_format:Y-m-d\TH:i'], // Waktu Periode Pendaftaran Ditutup
        'tahun_lulus_akhir' => ['validation' => 'date_format:Y'], // Tahun Terakhir Kelulusan
        'tanggal_minimal_batas_lahir' => ['type'=> 'date', 'validation' => 'date_format:Y-m-d'], // Tanggal Batas Lahir Minimal
        'tanggal_maksimal_batas_lahir' => ['type'=> 'date', 'validation' => 'date_format:Y-m-d'], // Tanggal Batas Lahir Maksimal
        'tanggal_awal_daftar_ulang' => ['type'=> 'timestamp', 'validation' => 'date_format:Y-m-d\TH:i'], // Tanggal Daftar Ulang Dimulai
        'tanggal_akhir_daftar_ulang' => ['type'=> 'timestamp', 'validation' => 'date_format:Y-m-d\TH:i'], // Tanggal Daftar Ulang Berakhir
        'waktu_pengumuman_kelulusan' => ['type'=> 'timestamp', 'validation' => 'date_format:Y-m-d\TH:i'], // Tanggal Pengumuman Lolos Seleksi Pendaftaran
        'waktu_pengumuman_nilai' => ['type'=> 'timestamp', 'validation' => 'date_format:Y-m-d\TH:i'], // Tanggal Pengumuman Nilai Seleksi Pendaftaran
        'apakah_tampilkan_daya_tampung' => ['type' => 'boolean'], // Kuota Ditampilkan?
        'apakah_tampilkan_nilai' => ['type' => 'boolean'], // Nilai Ditampilkan?
        'dapat_mengubah_prodi' => ['type' => 'boolean'], // Bisa Ubah Prodi?
        'dapat_pilih_prodi_sama' => ['type' => 'boolean'], // Bisa Pilih Prodi Yang Sama?
        'dapat_pilih_fakultas_sama' => ['type' => 'boolean'], // Bisa Pilih Fakultas Yang Sama?
        'keterangan_finalisasi' => ['maxlength' => 255], // Deskripsi Verifikasi / Finalisasi
        'waktu_akhir_finalisasi' => ['type'=> 'timestamp', 'validation' => 'date_format:Y-m-d\TH:i'], // Tanggal Akhir Verifikasi / Finalisasi
        'penilaian_rapor' => ['required' => true, 'maxlength' => 255, 'options' => self::REPORT_EVALUATIONS], // Penilaian Rapor (Yes, No, Optional)
        'batas_tanggal_va' => ['type' => 'integer', 'max' => '14'], // Batas Pembayaran VA Formulir
        'proses_kelulusan' => ['required' => true, 'maxlength' => 255, 'options' => self::QUALIFICATION_PROCESSES], // Proses Kualifikasi (Manual, Auto Recommended, Auto Qualified)
    ];

    private static function clearCache($model)
    {
        PeriodePendaftaranCache::destroy();
    }
}
