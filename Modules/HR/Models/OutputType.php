<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class OutputType extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr.output_types';

    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'code' => ['required' => true, 'maxlength' => 5, 'unique' => true],
        'name' => ['required' => true, 'maxlength' => 255],
    ];

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\OutputTypeFactory::new();
    }
}
