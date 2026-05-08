<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class KlasterPendanaanAgendaKegiatan extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.klaster_pendanaan_agenda_kegiatan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_klaster_pendanaan',
        'id_agenda_kegiatan',
        'waktu_mulai',
        'waktu_selesai',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_klaster_pendanaan' => ['required' => true, 'options' => KlasterPendanaan::class], // Klaster Pendanaan
        'id_agenda_kegiatan' => ['required' => true, 'options' => AgendaKegiatan::class],     // Tahapan Kegiatan
        'waktu_mulai' => ['type' => 'timestamp'],                                              // Waktu Awal Tahapan Kegiatan
        'waktu_selesai' => ['type' => 'timestamp'],                                             // Waktu Akhir Tahapan Kegiatan
    ];
}
