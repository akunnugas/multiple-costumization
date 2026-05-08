<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\DMS\Models\Dokumen;

class DokumenKerjasama extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kerjasama.dokumen_kerjasama';

    protected $fillable = [
        "id_kerjasama",
        "id_dokumen"
    ];

    public function kerjasama(): BelongsTo
    {
        return $this->belongsTo(Kerjasama::class);
    }

    public function dokumen(): BelongsTo 
    {
        return $this->belongsTo(Dokumen::class);    
    }
}
