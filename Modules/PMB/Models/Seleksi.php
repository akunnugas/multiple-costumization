<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class Seleksi extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.seleksi';

    protected $fillable = [
        'id_sebaran_prodi',
        'id_jenis_seleksi',
        'urutan_seleksi',
        'persentase_nilai',
        'waktu_mulai',
        'waktu_selesai',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_sebaran_prodi' => ['required' => true, 'options' => SebaranProdi::class], // Program Studi
        'id_jenis_seleksi' => ['required' => true, 'options' => SeleksiJenis::class], // Jenis Seleksi
        'urutan_seleksi' => ['required' => true, 'type' => 'numeric', 'maxlength' => 100], // Urutan
        'persentase_nilai' => ['required' => true, 'type' => 'numeric', 'min' => 1, 'max' => 100], // Persentase Nilai
        'waktu_mulai' => ['type' => 'timestamp'], // Tgl. Mulai
        'waktu_selesai' => ['type' => 'timestamp'], // Tgl. Selesai
    ];

    /**
     * Relation to program distribution.
     */
    public function sebaranProdi()
    {
        return $this->belongsTo(SebaranProdi::class);
    }
}
