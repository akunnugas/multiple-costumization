<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class KlasterPendanaanOutputPenelitian extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.klaster_pendanaan_output_penelitian';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_klaster_pendanaan',
        'id_jenis_output_penelitian',
        'apakah_wajib',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_klaster_pendanaan' => ['required' => true, 'options' => KlasterPendanaan::class],                 // Klaster Pendanaan
        'id_jenis_output_penelitian' => ['required' => true, 'options' => JenisOutputPenelitian::class],      // Output
        'apakah_wajib' => ['required' => true, 'type' => 'boolean'],                                          // Wajib?
    ];
}
