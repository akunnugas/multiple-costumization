<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Extensions\Models\IndonesianModel;

class JawabanPeserta extends IndonesianModel
{

    protected $table = 'kerjasama.jawaban_peserta';

    protected $fillable = [
        'peserta_id',
        'pertanyaan_id',
        'opsi_jawaban_id',
        'jawaban',
    ];

    const RULES = [
        'peserta_id' => ['required' => true, 'type' => 'integer'],
        'pertanyaan_id' => ['required' => true, 'type' => 'integer'],
        'opsi_jawaban_id' => ['nullable' => true, 'type' => 'integer'],
        'jawaban' => ['nullable' => true],
    ];

    // Relationships
    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'peserta_id');
    }

    public function pertanyaan()
    {
        return $this->belongsTo(Pertanyaan::class, 'pertanyaan_id');
    }

    public function opsiJawaban()
    {
        return $this->belongsTo(OpsiJawaban::class, 'opsi_jawaban_id');
    }

    /**
     * Get the title of the related pertanyaan.
     */
    public function getPertanyaanTitleAttribute()
    {
        return $this->pertanyaan ? $this->pertanyaan->pertanyaan : null;
    }
}