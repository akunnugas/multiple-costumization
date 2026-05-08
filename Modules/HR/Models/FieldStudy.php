<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Traits\TreeStructure;

class FieldStudy extends IndonesianModel
{
    use HasFactory, SoftDeletes, TreeStructure;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr.field_studies';

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'depth',
        'info_left',
        'info_right',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'parent_id' => ['required' => false, 'options' => FieldStudy::class],
        'code' => ['required' => true, 'maxlength' => 5],
        'name' => ['required' => true, 'maxlength' => 255],
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
        return \Modules\HR\Database\factories\FieldStudyFactory::new();
    }
}
