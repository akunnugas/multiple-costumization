<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\DMS\Models\Dokumen;
use Modules\Gate\Models\User;

class SeleksiNilai extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.seleksi_nilai';

    protected $fillable = [
        'id_pendaftar',
        'id_seleksi_jenis',
        'nilai_seleksi',
        'apakah_sesuai',
        'keterangan_nilai',
        'id_file_lampiran',
        'id_seleksi_jadwal',
        'dinilai_oleh',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_pendaftar' => ['required' => true, 'options' => Pendaftar::class], // Pendaftar
        'id_seleksi_jenis' => ['required' => true, 'options' => SeleksiJenis::class], // Jenis Seleksi
        'nilai_seleksi' => ['required' => true, 'type' => 'numeric', 'maxlength' => 100], // Nilai Seleksi
        'apakah_sesuai' => ['required' => true, 'type' => 'boolean'], // Lulus Seleksi?
        'keterangan_nilai' => ['maxlength' => 255], // Catatan Seleksi
        'id_file_lampiran' => ['options' => Dokumen::class], // Lampiran
        'id_seleksi_jadwal' => ['options' => SeleksiJadwal::class], // Jadwal Seleksi
        'dinilai_oleh' => ['options' => User::class], // User Penilai
    ];
}
