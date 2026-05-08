<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class Gelombang extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.gelombang';

    protected $fillable = ['nama_gelombang'];

    /**
     * Constant order for default options in ModelTrait.
     */
    const OPTION_ORDER = 'nama_gelombang';

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'nama_gelombang' => ['required' => true, 'unique' => true, 'maxlength' => 100], // Nama Gelombang
    ];
}
