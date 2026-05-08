<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class IndikatorSasaran extends IndonesianModel
{
    use SoftDeletes;
    
    const OPTION_ORDER = 'indikator asc';
    const OPTION_COLUMN = 'indikator';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kerjasama.indikator_sasaran';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'indikator',
        'keterangan',
        'volume',
        'satuan',
        'id_sasaran_kinerja',
        'isian_default'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'indikator' => ['required' => true],
        'keterangan' => ['required' => false, 'control' => 'textarea'],
        'volume' => ['required' => false],
        'satuan' => ['required' => false]
    ];
}
