<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\ProgramStudi;
use Modules\Core\Models\PerguruanTinggi;
use Modules\Core\Models\JenisInstitusi;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Wilayah;
use Modules\Core\Models\Sekolah;

class PendaftarPendidikan extends IndonesianModel
{
    use SoftDeletes, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.pendaftar_pendidikan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pendaftar',
        'id_jenjang_pendidikan',
        'id_provinsi',
        'id_kota',
        'id_jenis_institusi',
        'nama_institusi',
        'jurusan',
        'tahun_lulus',
        'id_sekolah',
        'id_perguruan_tinggi',
        'id_program_studi',
        'nisn',
        'nim',
        'nilai',
        'ipk',
        'sks',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pendaftar' => ['required' => true, 'options' => Pendaftar::class], // Pendaftar
        'id_jenjang_pendidikan' => ['required' => true, 'options' => JenjangPendidikan::class], // Jenjang
        'id_provinsi' => ['required' => true, 'options' => Wilayah::class], // Provinsi Institusi
        'id_kota' => ['required' => true, 'options' => Wilayah::class], // Kota Institusi
        'id_jenis_institusi' => ['required' => true, 'options' => JenisInstitusi::class], // Jenis Institusi
        'nama_institusi' => ['required' => true, 'maxlength' => 100], // Nama Institusi
        'jurusan' => ['maxlength' => 50], // Jurusan
        'tahun_lulus' => ['validation' => 'date_format:Y'], // Tahun Lulus
        'id_sekolah' => ['options' => Sekolah::class], // Sekolah
        'id_perguruan_tinggi' => ['options' => PerguruanTinggi::class], // Universitas
        'id_program_studi' => ['options' => ProgramStudi::class], // Program Studi
        'nisn' => ['maxlength' => 60], // NISN
        'nim' => ['maxlength' => 20], // NIM
        'nilai' => ['type' => 'numeric', 'min' => 0, 'max' => 100], // Nilai
        'ipk' => ['type' => 'numeric', 'min' => 0, 'max' => 4], // IPK
        'sks' => ['type' => 'numeric', 'min' => 0, 'max' => 200], // SKS
    ];
}
