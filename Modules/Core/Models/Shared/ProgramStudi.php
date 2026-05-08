<?php

namespace Modules\Core\Models\Shared;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class ProgramStudi extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'shared';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'program_studi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_prodi',
        'nama_prodi',
        'alamat_prodi',
        'telepon_prodi',
        'kode_sister',
        'id_perguruan_tinggi',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_prodi' => ['required' => true, 'maxlength' => 255], // Kode Prodi
        'nama_prodi' => ['required' => true, 'maxlength' => 255], // Nama Prodi
        'alamat_prodi' => ['maxlength' => 255], // Alamat
        'telepon_prodi' => ['maxlength' => 255], // No. Telp
        'kode_sister' => ['maxlength' => 255], // Id Sister
        'id_perguruan_tinggi' => ['required' => true, 'options' => PerguruanTinggi::class], // Perguruan Tinggi
    ];
}
