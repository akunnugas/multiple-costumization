<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class FunctionalPosition extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr.functional_positions';

    protected $fillable = [
        'academic_position_id',
        'position_level_id',
        'code',
        'name',
        'credit_number',
        'retirement_age',
        'position_emis_code',
        'dikti_id',
    ];

    const OPTION_ORDER = 'name';
    const OPTION_COLUMN = 'name';

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'academic_position_id' => ['required' => false, 'options' => JabatanAkademik::class],
        'position_level_id' => ['required' => false, 'options' => PositionLevel::class],
        'code' => ['required' => true, 'maxlength' => 5, 'unique' => true],
        'name' => ['required' => true, 'maxlength' => 255],
        'credit_number' => ['required' => true, 'type' => 'integer'],
        'retirement_age' => ['required' => true, 'type' => 'integer'],
        'position_emis_code' => ['required' => false, 'maxlength' => 5],
        'dikti_id' => ['required' => false, 'type' => 'integer'],
    ];

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\FunctionalPositionFactory::new();
    }
}
