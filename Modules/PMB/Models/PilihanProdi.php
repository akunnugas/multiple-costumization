<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Gate\Models\User;

class PilihanProdi extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.pilihan_prodi';

    protected $fillable = [
        'id_pendaftar',
        'id_sebaran_prodi',
        'urutan_pilihan',
        'status_pilihan',
        'nilai_pilihan',
        'apakah_rekomendasi',
        'apakah_afirmasi',
        'apakah_afirmasi_disetujui',
        'afirmasi_disetujui_oleh',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_pendaftar' => ['required' => true, 'options' => Pendaftar::class], // Pendaftar
        'id_sebaran_prodi' => ['required' => true, 'options' => SebaranProdi::class], // Sebaran Prodi
        'urutan_pilihan' => ['required' => true, 'type' => 'numeric', 'maxlength' => 100], // Urutan Pilihan Prodi
        'status_pilihan' => ['maxlength' => 50], // Status Pilihan Prodi
        'nilai_pilihan' => ['type' => 'numeric', 'maxlength' => 1000], // Nilai
        'apakah_rekomendasi' => ['type' => 'boolean'], // Rekomendasi?
        'apakah_afirmasi' => ['type' => 'boolean'], // Titipan?
        'apakah_afirmasi_disetujui' => ['type' => 'boolean'], // Titipan Disetujui?
        'afirmasi_disetujui_oleh' => ['options' => User::class], // User Yang Menyetujui Titipan
    ];
}
