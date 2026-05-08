<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class RecognitionType extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr.recognition_types';

    protected $fillable = [
        'name',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'name' => ['required' => true, 'maxlength' => 255],
    ];

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\RecognitionTypeFactory::new();
    }
}
