<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class TahunAkademik extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.tahun_akademik';

    protected $fillable = ['tahun'];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'tahun' => ['required' => true, 'unique' => true],
    ];

    /**
     * Display options.
     * @return array
     */
    public static function options()
    {
        return static::orderBy('tahun', 'desc')->get(['id', 'tahun'])->pluck('tahun', 'id')->toArray();
    }

    /**
     * Display option value.
     * @param $id
     * @return mixed
     */
    public static function optionValue($id)
    {
        return static::where('id', $id)->first(['tahun'])->tahun;
    }
}
