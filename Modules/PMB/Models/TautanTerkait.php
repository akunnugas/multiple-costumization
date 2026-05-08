<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class TautanTerkait extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.tautan_terkait';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama_tautan',
        'link_tautan',
        'urutan_tautan',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'nama_tautan' => ['required' => true, 'maxlength' => 255], // Nama Link
        'link_tautan' => ['required' => true, 'maxlength' => 255], // URL Link
        'urutan_tautan' => ['type' => 'integer'], // Urutan Link
    ];
}
