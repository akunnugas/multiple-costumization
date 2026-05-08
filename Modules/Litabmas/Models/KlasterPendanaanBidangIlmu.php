<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class KlasterPendanaanBidangIlmu extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.klaster_pendanaan_bidang_ilmu';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_klaster_pendanaan',
        'id_bidang_ilmu',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_klaster_pendanaan' => ['required' => true, 'options' => KlasterPendanaan::class], // Klaster Pendanaan
        'id_bidang_ilmu' => ['required' => true, 'options' => BidangIlmu::class],             // Bidang Ilmu
    ];


    /**
     * Relasi ke master tema
     */
    public function temaKegiatan(): BelongsToMany
    {
        return $this->belongsToMany(
            TemaKegiatan::class,
            'litabmas.klaster_pendanaan_bidang_ilmu_tema',
            'id_klaster_pendanaan_bidang_ilmu',
            'id_tema_kegiatan'
        )
            ->where('litabmas.klaster_pendanaan_bidang_ilmu_tema.waktu_dihapus', null);
    }

    /**
     * Relasi ke table mappingan tema
     *
     * @return HasMany
     */
    public function pivotTemaKegiatan(): HasMany
    {
        return $this->hasMany(
            KlasterPendanaanBidangIlmuTema::class,
            'id_klaster_pendanaan_bidang_ilmu'
        );
    }
}
