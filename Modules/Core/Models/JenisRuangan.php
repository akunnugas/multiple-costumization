<?php

namespace Modules\Core\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class JenisRuangan extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.jenis_ruangan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_jenis_ruangan',
        'nama_jenis_ruangan',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_jenis_ruangan' => ['required' => true, 'unique' => true, 'maxlength' => 255], // Kode Jenis Ruang
        'nama_jenis_ruangan' => ['required' => true, 'maxlength' => 255], // Nama Jenis Ruang
    ];
}
