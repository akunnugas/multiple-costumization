<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class StatusPerkawinan extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.status_perkawinan';

    protected $fillable = [
        'kode_status_perkawinan',
        'nama_status_perkawinan',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_status_perkawinan' => ['required' => true, 'maxlength' => 5, 'unique' => true],
        'nama_status_perkawinan' => ['required' => true, 'maxlength' => 255],
    ];
}
