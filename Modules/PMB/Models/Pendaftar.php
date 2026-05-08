<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\PeriodeAkademik;
use Modules\Core\Models\Biodata;
use Modules\Gate\Models\User;

class Pendaftar extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.pendaftar';

    const QUALIFIED_STATUS = [
        'recommendation' => 'Rekomendasi',
        'qualified' => 'Diterima',
        'reserve' => 'Cadangan',
        'approved-reserve' => 'Cadangan Diterima',
        'rejected' => 'Ditolak',
    ];
    const UTM_SOURCE = [
        'brosur' => 'Brosur',
        'teman' => 'Teman',
        'alumni' => 'Alumni',
        'google' => 'Google',
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'x' => 'X (Twitter)',
        'radio' => 'Radio',
    ];

    protected $fillable = [
        'kode_pendaftar',
        'id_biodata',
        'id_periode_pendaftaran',
        'id_periode_akademik',
        'id_mahasiswa',
        'sumber_data',
        'status_lulus',
        'id_prodi_lulus',
        'id_prodi_diminati',
        'direkomendasikan_oleh',
        'utm_source',
        'apakah_import_nim',
        'waktu_registrasi',
        'tanggal_daftar_ulang',
        'waktu_finalisasi',
        'waktu_aktif',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_pendaftar' => ['maxlength' => 50, 'unique' => true], // Kode Pendaftar
        'id_biodata' => ['options' => Biodata::class], // Data Pengguna
        'id_periode_pendaftaran' => ['options' => PeriodePendaftaran::class], // Periode Pendaftaran
        'id_periode_akademik' => ['required' => true, 'options' => PeriodeAkademik::class], // Periode Akademik
        'id_mahasiswa' => ['options' => []], // Mahasiswa
        'sumber_data' => ['maxlength' => 100], // Tipe Sumber Data
        'status_lulus' => ['maxlength' => 50], // Status Kelulusan Seleksi Pendaftaran
        'id_prodi_lulus' => ['options' => UnitKerja::class], // Prodi Kelulusan Seleksi Pendaftaran
        'id_prodi_diminati' => ['options' => UnitKerja::class], // Prodi Kelulusan Seleksi Pendaftaran
        'direkomendasikan_oleh' => ['options' => User::class], // User Perekomendasi
        'utm_source' => ['maxlength' => 50], // Sumber Informasi Pendaftaran
        'apakah_import_nim' => ['type' => 'boolean'], // NIM di-import?
        'waktu_registrasi' => ['type' => 'timestamp'], // Tanggal Mendaftar
        'tanggal_daftar_ulang' => ['type' => 'date', 'validation' => 'date_format:Y-m-d'], // Tanggal Daftar Ulang
        'waktu_finalisasi' => ['type' => 'timestamp'], // Tanggal Verifikasi
        'waktu_aktif' => ['type' => 'timestamp'], // Tanggal Aktif
    ];
}
