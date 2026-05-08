<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class DataPengisianLED extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.data_pengisian_led';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengisian_indikator',
        'id_indikator_evaluasi_diri',
        'data_pengisian_led',
        'komentar',
        'key_points',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengisian_indikator' => ['required' => true, 'options' => PengisianIndikator::class], // Pengisian Indikator
        'data_pengisian_led' => ['required' => false], // Data Butir
    ];

    /**
     * Get Record by filling_indicator
     *
     * @param filling_indicator
     *
     */
    public static function getRecordByPengisianIndikator($id)
    {
        $data = self::where([
            'id_pengisian_indikator' => $id
        ])->get();

        if (!$data)
            return [];

        return $data;
    }
}
