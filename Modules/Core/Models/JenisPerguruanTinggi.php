<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class JenisPerguruanTinggi extends IndonesianModel
{
    use SoftDeletes;

    const OPTION_ORDER = 'kode_jenis_pt asc';
    const OPTION_COLUMN = 'nama_jenis_pt';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.jenis_perguruan_tinggi';

    protected $fillable = [
        'kode_jenis_pt',
        'nama_jenis_pt',
        'kategori'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_jenis_pt' => ['required' => true, 'maxlength' => 10],
        'nama_jenis_pt' => ['required' => true, 'maxlength' => 255],
        'kategori' => ['required' => false, 'maxlength' => 255],
    ];
}
