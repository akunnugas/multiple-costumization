<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class MappingPanduan extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.mapping_panduan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengisian_panduan',
        'id_penilaian_panduan'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengisian_panduan' => ['required' => true, 'options' => PengisianPanduan::class],
        'id_penilaian_panduan' => ['required' => true, 'options' => PenilaianPanduan::class],
    ];
}
