<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Mahasiswa;

class PelaksanaKegiatan extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kerjasama.pelaksana_kegiatan';

    protected $fillable = [
        'id_kegiatan',
        'id_mahasiswa',
        'program_studi',
        'informasi_tambahan',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_mahasiswa' => [
            'required' => true, 
            'control' => 'autocomplete', 
            'placeholder' => 'Cari mahasiswa berdasarkan nim atau nama', 
            'options' => []
        ],
        'program_studi' => [
            'required' => true,
            'control' => 'text'
        ],
        'informasi_tambahan' => [
            'required' => false,
            'control' => 'textarea'
        ],
    ];

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa');
    }
}
