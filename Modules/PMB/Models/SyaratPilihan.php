<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class SyaratPilihan extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.syarat_pilihan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_syarat',
        'nama_pilihan',
        'poin_pilihan',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_syarat' => ['required' => true, 'options' => Syarat::class], // Syarat Seleksi
        'nama_pilihan' => ['required' => true, 'maxlength' => 255], // Nama Pilihan Syarat
        'poin_pilihan' => ['required' => true, 'type' => 'numeric', 'min' => 1, 'max' => 100], // Poin Pilihan Syarat
    ];
}
