<?php

namespace Modules\Core\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class Bahasa extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.bahasa';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_bahasa',
        'nama_bahasa',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_bahasa' => ['required' => true, 'maxlength' => 3, 'unique' => true], // Kode
        'nama_bahasa' => ['required' => true, 'maxlength' => 255], // Nama Bahasa
    ];
}
