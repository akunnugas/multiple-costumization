<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Extensions\Models\IndonesianModel;

class MappingSasaranBentukKegiatan extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kerjasama.mapping_sasaran_bentuk_kegiatan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_bentuk_kegiatan',
        'id_sasaran_kinerja',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_bentuk_kegiatan' => ['required' => true],
        'id_sasaran_kinerja' => ['required' => true],
    ];

    public function bentukKegiatan(): BelongsTo
    {
        return $this->belongsTo(BentukKegiatan::class, 'id_bentuk_kegiatan');
    }

    public function sasaranKinerja(): BelongsTo
    {
        return $this->belongsTo(SasaranKinerja::class, 'id_sasaran_kinerja');
    }
}
