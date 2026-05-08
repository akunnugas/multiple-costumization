<?php

namespace Modules\Core\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class Gedung extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.gedung';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_gedung',
        'nama_gedung',
        'alamat_gedung',
        'telepon_gedung',
        'jumlah_lantai',
        'jumlah_ruangan',
        'id_kampus',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_gedung' => ['required' => true, 'maxlength' => 255], // Kode Gedung
        'nama_gedung' => ['required' => true, 'maxlength' => 255], // Nama Ruang
        'alamat_gedung' => ['maxlength' => 255], // Alamat
        'telepon_gedung' => ['maxlength' => 255], // No. Telp
        'jumlah_lantai' => ['type' => 'integer'], // Jumlah Lantai
        'jumlah_ruangan' => ['type' => 'integer'], // Jumlah Ruangan
        'id_kampus' => ['options' => Kampus::class], // Kampus
    ];
}
