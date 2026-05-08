<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\Traits\TreeStructure;

class StructuralPosition extends IndonesianModel
{
    use HasFactory, SoftDeletes, TreeStructure;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr.structural_positions';

    protected $fillable = [
        'parent_id',
        'organization_id',
        'min_position_level_id',
        'max_position_level_id',
        'structural_position_type_id',
        'echelon_id',
        'name',
        'code',
        'email',
        'description',
        'is_active',
        'is_leader',
        'abbreviation',
        'depth',
        'info_left',
        'info_right',
    ];

    const OPTION_ORDER = 'name';
    const OPTION_COLUMN = 'name';

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'parent_id' => ['required' => false, 'options' => StructuralPosition::class],
        'organization_id' => ['required' => false, 'options' => UnitKerja::class],
        'min_position_level_id' => ['required' => false, 'options' => PositionLevel::class],
        'max_position_level_id' => ['required' => false, 'options' => PositionLevel::class],
        'structural_position_type_id' => ['required' => false, 'options' => StructuralPositionType::class],
        'echelon_id' => ['required' => false, 'options' => Echelon::class],
        'name' => ['required' => true, 'maxlength' => 255],
        'code' => ['required' => true, 'maxlength' => 10],
        'email' => ['required' => false, 'maxlength' => 100, 'type' => 'email'],
        'description' => ['required' => false, 'maxlength' => 255],
        'is_active' => ['required' => true, 'type' => 'boolean'],
        'is_leader' => ['required' => true, 'type' => 'boolean'],
        'abbreviation' => ['required' => false, 'maxlength' => 255],
        'depth' => ['required' => false, 'type' => 'integer'],
        'info_left' => ['required' => false, 'type' => 'integer'],
        'info_right' => ['required' => false, 'type' => 'integer'],
    ];

    protected static function defineDepthField()
    {
        return 'depth';
    }

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\StructuralPositionFactory::new();
    }
}
