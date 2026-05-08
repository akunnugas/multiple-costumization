<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class Echelon extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr.echelons';

    protected $fillable = [
        'code',
        'name',
        'is_active'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'code' => ['required' => true, 'maxlength' => 5, 'unique' => true],
        'name' => ['required' => true, 'maxlength' => 255],
        'is_active' => ['required' => true, 'type' => 'boolean'],
    ];

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\EchelonFactory::new();
    }
}
