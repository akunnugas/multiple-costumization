<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Extensions\Models\IndonesianModel;

class LogODS extends IndonesianModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.log_ods';

    protected $fillable = [
        'id_pendaftar', 'id_aktivitas', 'keterangan_log'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_pendaftar' => ['required' => true, 'type' => 'integer', 'options' => Pendaftar::class], // Kode Pendaftar
        'id_aktivitas' => ['required' => true, 'type' => 'integer', 'options' => Aktivitas::class], // Kode Aktivitas
        'keterangan_log' => ['nullable' => true, 'maxlength' => 255], // Keterangan
    ];
}
