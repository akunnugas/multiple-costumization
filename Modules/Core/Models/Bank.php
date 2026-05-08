<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class Bank extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.bank';

    protected $fillable = ['nama_bank', 'kode_bank'];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_bank' => ['required' => true, 'maxlength' => 5, 'unique' => true],
        'nama_bank' => ['required' => true, 'maxlength' => 100],
    ];
}
