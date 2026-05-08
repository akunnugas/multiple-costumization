<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class PeriodeAkademik extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.periode_akademik';

    protected $fillable = [
        'kode_periode',
        'nama_periode',
        'id_tahun_akademik',
        'waktu_mulai',
        'waktu_selesai',
    ];

    /**
     * Constant order for default options in ModelTrait.
     */
    const OPTION_ORDER = 'kode_periode desc';

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_periode' => ['required' => true, 'maxlength' => 20],
        'nama_periode' => ['required' => true, 'maxlength' => 50],
        'id_tahun_akademik' => ['required' => true, 'options' => TahunAkademik::class],
        'waktu_mulai_periode' => ['required' => true, 'type' => 'date', 'validation' => 'date_format:Y-m-d'],
        'waktu_selesai_periode' => ['required' => true, 'type' => 'date', 'validation' => 'date_format:Y-m-d'],
    ];
}
