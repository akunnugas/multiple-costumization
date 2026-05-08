<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class SyaratPendaftaran extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.syarat_pendaftaran';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_periode_pendaftaran',
        'id_seleksi_syarat',
        'id_jenis_syarat',
        'apakah_wajib',
        'apakah_upload',
        'jumlah_dokumen',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_periode_pendaftaran' => ['required' => true, 'options' => PeriodePendaftaran::class], // Periode Pendaftaran
        'id_seleksi_syarat' => ['required' => true, 'options' => Syarat::class], // Syarat Seleksi
        'id_jenis_syarat' => ['required' => true, 'options' => SyaratJenis::class], // Jenis Syarat
        'apakah_wajib' => ['type' => 'boolean'], // Wajib?
        'apakah_upload' => ['type' => 'boolean'], // Unggah Dokumen?
        'jumlah_dokumen' => ['type' => 'integer'], // Jumlah Dokumen
    ];
}
