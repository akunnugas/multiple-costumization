<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class Agama extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.agama';

    protected $fillable = ['kode_agama', 'nama_agama', 'kode_dikti'];

    const OPTION_ORDER = 'id';
    const OPTION_COLUMN = 'nama_agama';

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_agama' => ['required' => false, 'maxlength' => 10, 'unique' => true],
        'nama_agama' => ['required' => true, 'maxlength' => 100],
        'kode_dikti' => ['required' => false],
    ];
}
