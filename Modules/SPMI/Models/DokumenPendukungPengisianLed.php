<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\DMS\Models\Dokumen;

class DokumenPendukungPengisianLed extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.dokumen_pendukung_pengisian_led';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'pengisian_indikator_id',
        'id_indikator_evaluasi_diri',
        'id_dokumen',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'pengisian_indikator_id' => ['required' => true, 'type' => 'integer'], 
        'id_indikator_evaluasi_diri' => ['required' => true, 'options' => IndikatorEvaluasiDiri::class], 
        'id_dokumen' => ['required' => true, 'options' => Dokumen::class], 
    ];
}
