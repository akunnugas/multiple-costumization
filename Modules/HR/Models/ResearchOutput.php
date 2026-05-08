<?php

namespace Modules\HR\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class ResearchOutput extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr.research_outputs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'code' => ['required' => true, 'maxlength' => 5, 'unique' => true], // Kode
        'name' => ['required' => true, 'maxlength' => 255], // Nama Output Penelitian
    ];

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\ResearchOutputFactory::new();
    }
}
