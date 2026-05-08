<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class Syarat extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.syarat';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_syarat',
        'nama_syarat',
        'poin_syarat',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_syarat' => ['required' => true, 'maxlength' => 10, 'unique' => true], // Kode Syarat Seleksi
        'nama_syarat' => ['required' => true, 'maxlength' => 255], // Nama Syarat Seleksi
        'poin_syarat' => ['required' => true, 'type' => 'numeric', 'min' => 1, 'max' => 100], // Poin Syarat Seleksi
    ];
}
