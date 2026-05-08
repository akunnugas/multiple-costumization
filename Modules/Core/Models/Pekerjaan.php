<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class Pekerjaan extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.pekerjaan';

    protected $fillable = [
        'nama_pekerjaan', 'kode_emis', 'kode_emis_siswa', 'kode_emis_lulusan', 'kode_sister'
    ];

    const OPTION_ORDER = 'nama_pekerjaan';

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'nama_pekerjaan' => ['required' => true, 'maxlength' => 255],
        'kode_emis' => ['maxlength' => 2],
        'kode_emis_siswa' => ['maxlength' => 2],
        'kode_emis_lulusan' => ['maxlength' => 2],
        'kode_sister' => ['maxlength' => 20],
    ];
}
