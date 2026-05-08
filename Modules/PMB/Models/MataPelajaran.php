<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class MataPelajaran extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.mata_pelajaran';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama_mata_pelajaran',
        'nilai_minimal_lulus',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'nama_mata_pelajaran' => ['required' => true, 'unique' => true, 'maxlength' => 255], // Nama Mata Pelajaran
        'nilai_minimal_lulus' => ['type' => 'numeric', 'min' => 1, 'max' => 100], // Nilai Minimal Lulus
    ];

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'nama_mata_pelajaran asc';
    const OPTION_COLUMN = 'nama_mata_pelajaran';
}
