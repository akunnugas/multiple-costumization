<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class SumberPendanaanAgendaKegiatan extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.sumber_pendanaan_agenda_kegiatan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_sumber_pendanaan',
        'id_agenda_kegiatan',
        'apakah_aktif',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_sumber_pendanaan' => ['required' => true, 'options' => SumberPendanaan::class],   // Sumber Pendanaan
        'id_agenda_kegiatan' => ['required' => true, 'options' => AgendaKegiatan::class],     // Tahapan Kegiatan
        'apakah_aktif' => ['required' => false, 'type' => 'boolean'],                            // Aktif
    ];
}
