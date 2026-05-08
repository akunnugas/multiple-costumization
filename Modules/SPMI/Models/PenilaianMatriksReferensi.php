<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class PenilaianMatriksReferensi extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.penilaian_matriks_referensi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_penilaian_matriks',
        'id_butir_referensi',
        'jenis_referensi',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_penilaian_matriks' => ['required' => true, 'options' => PenilaianMatriks::class], // Assessment Matrix
        'id_butir_referensi' => ['required' => true], // Reference Item
        'jenis_referensi' => ['required' => true, 'maxlength' => 255], // Reference Type (pr: Laporan Kinerja, se: Evaluasi Diri)
    ];

    public static function getSelectedItems($PenilaianMatriksId)
    {
        return self::where('id_penilaian_matriks', $PenilaianMatriksId)->pluck('id_butir_referensi')->toArray();
    }
}
