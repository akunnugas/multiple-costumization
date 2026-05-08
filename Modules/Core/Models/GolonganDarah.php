<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class GolonganDarah extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.golongan_darah';

    protected $fillable = [
        'kode_golongan',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_golongan' => ['required' => true, 'maxlength' => 5, 'unique' => true],
    ];
}
