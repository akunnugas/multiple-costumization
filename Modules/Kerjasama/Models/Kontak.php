<?php

namespace Modules\Kerjasama\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class Kontak extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kerjasama.kontak';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama_kontak',
        'jabatan',
        'telepon',
        'email',
        'id_mitra'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'nama_kontak' => ['required' => true, 'max' => 255],
        'jabatan' => ['required' => true, 'max' => 255],
        'telepon' => ['required' => true, 'validation' => 'numeric|digits_between:10,20'],
        'email' => ['required' => true, 'max' => 255, 'validation' => 'email'],
    ];
}
