<?php

namespace Modules\Core\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class Kampus extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.kampus';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_kampus',
        'nama_kampus',
        'alamat_kampus',
        'telepon_kampus',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_kampus' => ['required' => true, 'maxlength' => 255], // Kode Gedung
        'nama_kampus' => ['required' => true, 'maxlength' => 255], // Nama Ruang
        'alamat_kampus' => ['maxlength' => 255], // Alamat
        'telepon_kampus' => ['maxlength' => 255], // No. Telp
    ];
}
