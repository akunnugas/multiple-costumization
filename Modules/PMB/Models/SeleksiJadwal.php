<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class SeleksiJadwal extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.seleksi_jadwal';

    protected $fillable = [
        'id_seleksi_ruangan',
        'id_seleksi',
        'waktu_mulai',
        'waktu_selesai',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_seleksi_ruangan' => ['options' => SeleksiRuangan::class], // Ruangan
        'id_seleksi' => ['required' => true, 'options' => Seleksi::class], // Seleksi Prodi
        'waktu_mulai' => ['validation' => 'date_format:Y-m-d H:i:sO'], // Waktu Seleksi Dimulai
        'waktu_selesai' => ['validation' => 'date_format:Y-m-d H:i:sO'], // Waktu Seleksi Berakhir
    ];
}
