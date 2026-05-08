<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class Suku extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.suku';

    protected $fillable = ['nama_suku'];

    const OPTION_ORDER = 'nama_suku';
    const OPTION_COLUMN = 'nama_suku';

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'nama_suku' => ['required' => true, 'unique' => true, 'maxlength' => 255],
    ];
}
