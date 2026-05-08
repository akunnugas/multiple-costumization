<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Extensions\Models\IndonesianModel;

class OpsiJawaban extends IndonesianModel
{
    use SoftDeletes;

    protected $table = 'kerjasama.opsi_jawaban';

    protected $fillable = [
        'pertanyaan_id',
        'urutan',
        'jawaban',
    ];

    const RULES = [
        'pertanyaan_id' => ['required' => true, 'type' => 'integer'],
        'urutan'        => ['nullable' => true, 'maxlength' => 255],
        'jawaban'       => ['required' => true, 'maxlength' => 255],
    ];

    public function pertanyaan(): BelongsTo
    {
        return $this->belongsTo(Pertanyaan::class, 'pertanyaan_id');
    }
}
