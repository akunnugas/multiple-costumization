<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Biodata;

class SkAuditorPegawai extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.sk_auditor_pegawai';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_sk_auditor',
        'id_personil',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_sk_auditor' => ['required' => true, 'options' => SkAuditor::class], //
        'id_personil' => ['required' => true, 'options' => Biodata::class], //
    ];
}
