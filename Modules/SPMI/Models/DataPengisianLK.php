<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class DataPengisianLK extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.data_pengisian_lk';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengisian_indikator',
        'id_indikator_laporan_kinerja',
        'data_pengisian_lk',
        'data_pengisian_lk_awal', // Hanya untuk keperluan perbaikan data, tidak boleh digunakan untuk keperluan lainnya
        'jenis_data_pengisian_lk',
        'teks_pengisian',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengisian_indikator' => ['required' => true, 'options' => PengisianIndikator::class], // Pengisian Indikator
        'data_pengisian_lk' => ['required' => true], // Data Butir
        'jenis_data_pengisian_lk' => ['required' => false], // Jenis Data Pengisian LK
        'teks_pengisian' => ['required' => false], // Teks Pengisian
    ];

    /**
     * Get Record by filling_indicator
     *
     * @param filling_indicator
     *
     */
    public static function getRecordByPengisianIndikator($id)
    {
        $data = DataPengisianLK::where([
            'id_pengisian_indikator' => $id
        ])->get();

        if (!$data)
            return [];

        return $data;
    }
}
