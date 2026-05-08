<?php

namespace Modules\Core\Models\Shared;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class PerguruanTinggi extends IndonesianModel
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
    protected $table = 'perguruan_tinggi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_pt',
        'nama_pt',
        'alamat_pt',
        'telepon_pt',
        'kode_sister',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_pt' => ['required' => true, 'maxlength' => 255], // Kode Universitas
        'nama_pt' => ['required' => true, 'maxlength' => 255], // Nama Universitas
        'alamat_pt' => ['maxlength' => 255], // Alamat
        'telepon_pt' => ['maxlength' => 255], // No. Telp
        'kode_sister' => ['maxlength' => 255], // Id Sister
    ];
}
