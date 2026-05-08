<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class ProgramStudi extends IndonesianModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.program_studi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_perguruan_tinggi',
        'id_jenjang_pendidikan',
        'kode_prodi',
        'nama_prodi',
        'alamat_prodi',
        'telepon_prodi',
        'kode_sister',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_perguruan_tinggi' => ['required' => true, 'options' => PerguruanTinggi::class], // Universitas
        'id_jenjang_pendidikan' => ['options' => JenjangPendidikan::class], // Jenjang
        'kode_prodi' => ['required' => true, 'maxlength' => 20], // Kode Program Studi
        'nama_prodi' => ['required' => true, 'maxlength' => 100], // Nama Program Studi
        'alamat_prodi' => ['maxlength' => 100], // Alamat Program Studi
        'telepon_prodi' => ['maxlength' => 20], // No. Telpon Program Studi
        'kode_sister' => ['maxlength' => 255], // Kode SISTER
    ];
}
