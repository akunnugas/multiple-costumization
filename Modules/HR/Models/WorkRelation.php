<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class WorkRelation extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr.work_relations';

    protected $fillable = [
        'code',
        'name',
        'is_active',
        'is_pns',
        'is_permanent',
        'ref_key_siakad'
    ];

    const OPTION_ORDER = 'name';
    const OPTION_COLUMN = 'name';

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'code' => ['required' => true, 'maxlength' => 5, 'unique' => true],
        'name' => ['required' => true, 'maxlength' => 255],
        'is_active' => ['required' => true, 'type' => 'boolean'],
        'is_pns' => ['required' => true, 'type' => 'boolean'],
        'is_permanent' => ['required' => false, 'type' => 'boolean']
    ];

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\WorkRelationFactory::new();
    }
}
