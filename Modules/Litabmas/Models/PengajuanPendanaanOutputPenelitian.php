<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class PengajuanPendanaanOutputPenelitian extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan_output_penelitian';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan',
        'id_jenis_output_penelitian',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan' => ['required' => true, 'options' => PengajuanPendanaan::class],          // Pengajuan Pendanaan
        'id_jenis_output_penelitian' => ['required' => true, 'options' => JenisOutputPenelitian::class],   // Output
    ];
}
