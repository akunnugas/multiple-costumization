<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class JenisStandar extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'kode_jenis_standar asc';
    const OPTION_COLUMN = 'nama_jenis_standar';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.jenis_standar';

    protected $fillable = [
        'kode_jenis_standar',
        'nama_jenis_standar',
        'apakah_data_default',
    ];

    protected $guarded = [
        'id',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_jenis_standar' => ['required' => true, 'maxlength' => 5, 'unique' => true],
        'nama_jenis_standar' => ['required' => true, 'maxlength' => 255],
        'apakah_data_default' => ['required' => true, 'boolean' => true],
    ];

    protected static function newFactory()
    {
        return \Modules\SPMI\Database\factories\JenisStandarFactory::new();
    }
}
