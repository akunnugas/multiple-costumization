<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class KlasterPendanaanBidangIlmuTema extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.klaster_pendanaan_bidang_ilmu_tema';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_klaster_pendanaan_bidang_ilmu',
        'id_tema_kegiatan',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_klaster_pendanaan_bidang_ilmu' => ['required' => true, 'options' => KlasterPendanaanBidangIlmu::class],         // Klaster Pendanaan - Bidang Ilmu
        'id_tema_kegiatan' => ['required' => true, 'options' => TemaKegiatan::class],                                       // Tema Kegiatan
    ];
}
