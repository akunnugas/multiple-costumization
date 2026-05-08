<?php

namespace Modules\Core\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class Penghasilan extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.penghasilan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_penghasilan',
        'nama_penghasilan',
        'poin_kip',
        'kode_emis',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_penghasilan' => ['required' => true, 'maxlength' => 255], // Kode
        'nama_penghasilan' => ['required' => true, 'maxlength' => 255], // Nama Penghasilan
        'poin_kip' => ['required' => true, 'type' => 'numeric', 'maxlength' => 255], // Poin KIP
        'kode_emis' => ['maxlength' => 255], // Kode EMIS Penghasilan
    ];
}
