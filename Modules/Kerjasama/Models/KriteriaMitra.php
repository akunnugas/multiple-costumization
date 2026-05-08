<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class KriteriaMitra extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'klasifikasi_mitra asc';
    const OPTION_COLUMN = 'klasifikasi_mitra';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kerjasama.kriteria_mitra';

    protected $fillable = [
        'klasifikasi_mitra',
        'keterangan',
        'isian_default',
        'bobot'
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
        'klasifikasi_mitra' => ['required' => true, 'maxlength' => 255, 'unique_ci' => true],
        'keterangan' => ['required' => false],
    ];

    // protected static function newFactory()
    // {
    //     return \Modules\Kerjasama\Database\factories\KriteriaMitraFactory::new();
    // }
}
