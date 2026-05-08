<?php

namespace Modules\Core\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class Ruangan extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.ruangan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_ruangan',
        'nama_ruangan',
        'lokasi',
        'daya_tampung',
        'panjang',
        'lebar',
        'lantai',
        'id_unit_kerja',
        'id_gedung',
        'id_jenis_ruangan',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_ruangan' => ['required' => true, 'maxpanjang' => 255], // Kode Ruang
        'nama_ruangan' => ['required' => true, 'maxlength' => 255], // Nama Ruang
        'lokasi' => ['maxlength' => 255], // Lokasi
        'daya_tampung' => ['type' => 'integer'], // Daya Tampung
        'length' => ['type' => 'integer'], // Panjang
        'lebar' => ['type' => 'integer'], // Lebar
        'lantai' => ['type' => 'integer'], // Lantai
        'id_unit_kerja' => ['required' => true, 'options' => UnitKerja::class], // Unit
        'id_gedung' => ['options' => Gedung::class], // Gedung
        'id_jenis_ruangan' => ['options' => JenisRuangan::class], // Jenis Ruangan
    ];
}
