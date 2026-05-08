<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class AkreditasiStandar extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'kode_standar asc';
    const OPTION_COLUMN = 'nama_standar';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.akreditasi_standar';

    protected $fillable = [
        'kode_standar',
        'nama_standar',
        'id_jenis_standar',
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
        'kode_standar' => ['required' => true, 'maxlength' => 5],
        'nama_standar' => ['required' => true, 'maxlength' => 255],
        'id_jenis_standar' => ['required' => true],
        'apakah_data_default' => ['required' => true, 'boolean' => true],
    ];

    protected static function newFactory()
    {
        return \Modules\SPMI\Database\factories\AkreditasiStandarFactory::new();
    }

    protected static function uniqueColumns(): array
    {
        return [
            'kode_standar_per_jenis' => [
                'fields' => ['id_jenis_standar', 'kode_standar'],
                'defaultMessage' => 'Kode standar sudah digunakan untuk jenis standar ini.',
            ],
        ];
    }
}
