<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class IndikatorReferensi extends IndonesianModel
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.indikator_referensi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_indikator_evaluasi_diri',
        'id_pengisian_panduan',
        'id_indikator_laporan_kinerja',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_indikator_evaluasi_diri' => ['required' => false, 'options' => IndikatorEvaluasiDiri::class], // ID Indikator Evaluasi Diri
        'id_indikator_laporan_kinerja' => ['required' => true, 'options' => IndikatorLaporanKinerja::class], // ID Indikator Laporan Kinerja
        'id_pengisian_panduan' => ['required' => false], // Panduan Indicator
    ];
}
