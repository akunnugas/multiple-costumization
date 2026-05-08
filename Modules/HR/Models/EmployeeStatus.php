<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class EmployeeStatus extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr.employee_statuses';

    protected $fillable = [
        'code',
        'name',
        'emis_code',
        'is_active',
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
        'emis_code' => ['required' => false, 'maxlength' => 5],
        'is_active' => ['required' => true, 'type' => 'boolean'],
    ];

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\EmployeeStatusFactory::new();
    }
}
