<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class SeleksiKomposisi extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.seleksi_komposisi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_periode_pendaftaran', 'id_seleksi_jenis', 'id_seleksi_komponen', 'persentase_komposisi'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_periode_pendaftaran' => ['required' => true, 'options' => PeriodePendaftaran::class], // Periode Pendaftaran
        'id_seleksi_jenis' => ['required' => true, 'options' => SeleksiJenis::class], // Seleksi Pendaftaran
        'id_seleksi_komponen' => ['required' => true, 'options' => SeleksiKomponen::class], // Komposisi Seleksi
        'persentase_komposisi' => ['required' => true, 'type' => 'numeric', 'min' => 1, 'max' => 100], // Persentase
    ];
}
