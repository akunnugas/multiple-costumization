<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class AkreditasiPeringkat extends IndonesianModel
{
    use SoftDeletes;

    const OPTION_ORDER = 'kode_peringkat asc';
    const OPTION_COLUMN = 'nama_peringkat_akreditasi';

    const CODE_SUPERIOR = 'U';
    const CODE_VERY_GOOD = 'S';
    const CODE_GOOD = 'G';
    const CODE_MINIMUM = 'M';

    const CODES = [
        self::CODE_MINIMUM => 'Tidak Terakreditasi / Kadaluarsa',
        self::CODE_GOOD => 'Baik',
        self::CODE_VERY_GOOD => 'Baik Sekali',
        self::CODE_SUPERIOR => 'Unggul',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.akreditasi_peringkat';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_peringkat',
        'nama_peringkat_akreditasi',
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
        'kode_peringkat' => ['required' => true, 'maxlength' => 10], // Kode Peringkat
        'nama_peringkat_akreditasi' => ['required' => true, 'maxlength' => 255], // Nama Peringkat Akreditasi
        'nilai_minimal' => ['required' => true], // Nilai Minimal
        'nilai_maksimal' => ['required' => true], // Nilai Maksimal
        'deskripsi' => ['required' => false], // Keterangan
    ];
}
