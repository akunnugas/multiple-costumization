<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Extensions\Models\IndonesianModel;

class SebaranPilihan extends IndonesianModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.sebaran_pilihan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_sebaran_prodi',
        'pilihan',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_sebaran_prodi' => ['required' => true, 'options' => SebaranProdi::class], // Sebaran Program Studi
        'pilihan' => ['required' => true, 'options' => PeriodePendaftaran::OPTION_AMOUNT_OF_PROGRAMS], // Pilihan ke
    ];
}
