<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Models\JenisInstitusi;
use Modules\Core\Extensions\Models\IndonesianModel;

class SebaranAsalPendaftar extends IndonesianModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.sebaran_asal_pendaftar';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_sebaran_prodi',
        'id_jenis_institusi',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_sebaran_prodi' => ['required' => true, 'options' => SebaranProdi::class], // Sebaran Program Studi
        'id_jenis_institusi' => ['required' => true, 'options' => JenisInstitusi::class], // Jenis Institusi
    ];
}
