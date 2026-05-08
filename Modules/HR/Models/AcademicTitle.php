<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class AcademicTitle extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr.academic_titles';

    protected $fillable = [
        'name', // Nama Gelar
        'abbreviation' // Singkatan Gelar
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'name' => ['required' => true, 'maxlength' => 255],
        'abbreviation' => ['required' => true, 'maxlength' => 30],
    ];

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\AcademicTitleFactory::new();
    }
}
