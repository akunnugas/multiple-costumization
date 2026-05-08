<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class AkreditasiStatus extends IndonesianModel
{
    const OPTION_ORDER = 'kode_status asc';
    const OPTION_COLUMN = 'nama_status_akreditasi';

    const CODE_TERAKREDITASI = 'M';
    const CODE_TIDAK_TERAKREDITASI = 'TM';
    const CODE_UNGGUL = 'U';

    const CODES = [
        self::CODE_TIDAK_TERAKREDITASI => 'Tidak Terakreditasi',
        self::CODE_TERAKREDITASI => 'Terakreditasi',
        self::CODE_UNGGUL => 'Terakreditasi Unggul',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.akreditasi_status';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_status',
        'nama_status',
        'nilai_minimal',
        'nilai_maksimal',
        'deskripsi',
        'id_penilaian_panduan',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_status' => ['required' => true, 'maxlength' => 10], // Kode Status
        'nama_status' => ['required' => true, 'maxlength' => 255], // Nama Status Akreditasi
        'nilai_minimal' => ['required' => true], // Nilai Minimal
        'nilai_maksimal' => ['required' => true], // Nilai Maksimal
        'deskripsi' => ['required' => false], // Keterangan
    ];
}
