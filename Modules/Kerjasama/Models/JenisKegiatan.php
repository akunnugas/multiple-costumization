<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class JenisKegiatan extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'nama_jenis_kegiatan asc';
    const OPTION_COLUMN = 'nama_jenis_kegiatan';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kerjasama.jenis_kegiatan';

    protected $fillable = [
        'nama_jenis_kegiatan',
    ];

    protected $guarded = [
        'id',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'nama_jenis_kegiatan' => ['required' => true, 'maxlength' => 255, 'unique' => true],
    ];
}
