<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Extensions\Models\IndonesianModel;

class Pertanyaan extends IndonesianModel
{
    use SoftDeletes;

    protected $table = 'kerjasama.pertanyaan';

    protected $guarded = [];

    protected $casts = [
        'apakah_wajib' => 'boolean',
    ];

    const RULES = [
        'evaluasi_id'   => ['required' => true, 'type' => 'integer'],
        'nomor'         => ['required' => true, 'maxlength' => 255],
        'pertanyaan'    => ['required' => true],
        'rating'       => ['nullable' => true],
        'tipe'          => ['required' => true, 'maxlength' => 255],
        'deskripsi'     => ['nullable' => true],
        'apakah_wajib'  => ['type' => 'boolean', 'required' => true],
    ];

    public function evaluasi(): BelongsTo
    {
        return $this->belongsTo(Evaluasi::class, 'evaluasi_id');
    }

    public function opsiJawaban(): HasMany
    {
        return $this->hasMany(OpsiJawaban::class, 'pertanyaan_id')->orderBy('urutan');
    }
    public function jawabanPeserta()
    {
        return $this->hasMany(JawabanPeserta::class, 'pertanyaan_id');
    }
}
