<?php

namespace Modules\PMB\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Ruangan;

class SeleksiRuangan extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.seleksi_ruangan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_ruangan',
        'nama_ruangan',
        'daya_tampung',
        'id_ruangan',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_ruangan' => ['required' => true, 'maxlength' => 255], // Kode
        'nama_ruangan' => ['required' => true, 'maxlength' => 255], // Name
        'daya_tampung' => ['type' => 'integer'], // Daya Tampung
        'id_ruangan' => ['options' => Ruangan::class], // Ruangan
    ];
}
