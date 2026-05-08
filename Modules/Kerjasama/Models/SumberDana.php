<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class SumberDana extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'sumber_dana asc';
    const OPTION_COLUMN = 'sumber_dana';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kerjasama.sumber_dana';

    protected $fillable = [
        'sumber_dana',
        // 'keterangan',
        'isian_default'
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
        'sumber_dana' => ['required' => true, 'maxlength' => 255, 'unique_ci' => true],
        // 'keterangan' => ['required' => false],
    ];

    // protected static function newFactory()
    // {
    //     return \Modules\Kerjasama\Database\factories\SumberDanaFactory::new();
    // }
}
