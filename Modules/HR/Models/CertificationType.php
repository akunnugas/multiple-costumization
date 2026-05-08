<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class CertificationType extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr.certification_types';

    protected $fillable = [
        'feeder_id',
        'dikti_id',
        'sister_id',
        'code',
        'name',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'feeder_id' => ['required' => false, 'type' => 'integer'],
        'dikti_id' => ['required' => false, 'type' => 'integer'],
        'sister_id' => ['required' => false, 'maxlength' => 255],
        'code' => ['required' => true, 'maxlength' => 5, 'unique' => true],
        'name' => ['required' => true, 'maxlength' => 255],
    ];

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\CertificationTypeFactory::new();
    }
}
