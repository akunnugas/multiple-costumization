<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class DokumenPendukungPengisianLk extends IndonesianModel
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.dokumen_pendukung_pengisian_lk';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'pengisian_indikator_id',
        'id_indikator_laporan_kinerja',
        'id_dokumen',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'pengisian_indikator_id' => ['required' => true, 'type' => 'integer'], 
        'id_indikator_laporan_kinerja' => ['required' => true, 'type' => 'integer'], 
        'id_dokumen' => ['required' => true, 'type' => 'integer'], 
    ];
}
