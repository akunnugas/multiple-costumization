<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class Aktivitas extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.aktivitas';

    protected $fillable = [
        'nama_aktivitas', 'kode_aktivitas', 'urutan_aktivitas'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'nama_aktivitas' => ['required' => true, 'maxlength' => 255, 'unique' => true], // Nama Aktivitas
        'kode_aktivitas' => ['required' => true, 'maxlength' => 10, 'unique' => true], // Kode Aktivitas
        'urutan_aktivitas' => ['required' => true, 'type' => 'integer', 'min' => 0], // Urutan Aktivitas
    ];
}
