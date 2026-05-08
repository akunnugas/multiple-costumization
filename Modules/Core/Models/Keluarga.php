<?php

namespace Modules\Core\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class Keluarga extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.keluarga';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_keluarga',
        'id_biodata',
        'id_status_hubungan_keluarga',
        'nik',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'telepon',
        'jenis_kelamin',
        'id_jenjang_pendidikan',
        'id_pekerjaan',
        'id_penghasilan',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_keluarga' => ['maxlength' => 255], // Kode Anggota Keluarga
        'id_biodata' => ['required' => true, 'options' => Biodata::class], // Data Personal
        'id_status_hubungan_keluarga' => ['required' => true, 'options' => StatusHubunganKeluarga::class], // Status Hubungan Keluarga
        'nik' => ['maxlength' => 255], // NIK
        'nama' => ['required' => true, 'maxlength' => 255], // Nama Anggota Keluarga
        'tempat_lahir' => ['maxlength' => 255], // Tempat Lahir
        'tanggal_lahir' => ['type' => 'date'], // Tanggal Lahir
        'alamat' => ['maxlength' => 255], // Alamat
        'telepon' => ['required' => true, 'maxlength' => 255], // No. Telp
        'jenis_kelamin' => ['required' => true, 'maxlength' => 1], // Jenis Kelamin
        'id_jenjang_pendidikan' => ['options' => JenjangPendidikan::class], // Jenjang Pendidikan
        'id_pekerjaan' => ['options' => Pekerjaan::class], // Pekerjaan
        'id_penghasilan' => ['options' => Penghasilan::class], // Penghasilan
    ];
}
