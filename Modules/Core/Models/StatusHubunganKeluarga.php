<?php

namespace Modules\Core\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class StatusHubunganKeluarga extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.status_hubungan_keluarga';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_status_keluarga',
        'nama_status_keluarga',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_status_keluarga' => ['required' => true, 'maxlength' => 255], // Kode Status Hubungan Keluarga
        'nama_status_keluarga' => ['required' => true, 'maxlength' => 255], // Nama Status Hubungan Keluarga
    ];
}
