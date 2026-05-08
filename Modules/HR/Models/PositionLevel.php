<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class PositionLevel extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr.position_levels';

    protected $fillable = [
        'code',
        'name',
        'emis_code'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'code' => ['required' => true, 'maxlength' => 5, 'unique' => true],
        'name' => ['required' => true, 'maxlength' => 255],
        'emis_code' => ['required' => false, 'maxlength' => 5],
    ];

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\PositionLevelFactory::new();
    }
}
