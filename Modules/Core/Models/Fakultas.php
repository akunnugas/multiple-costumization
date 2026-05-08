<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class Fakultas extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.fakultas';

    protected $fillable = ['nama_fakultas', 'kode_fakultas'];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_fakultas' => ['required' => true, 'maxlength' => 20],
        'nama_fakultas' => ['required' => true, 'maxlength' => 100],
    ];
}
