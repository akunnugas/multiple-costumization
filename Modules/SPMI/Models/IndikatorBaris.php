<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Traits\TreeStructure;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IndikatorBaris extends IndonesianModel
{
    use HasFactory, TreeStructure;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.indikator_baris';

    const NUMBER_ALPHABET = 'A';
    const NUMBER_NUMBER = 'N';
    const NUMBER_ROMAWI = 'R';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama',
        'ref_key_akreditasi',
        'id_indikator_laporan_kinerja',
        'jenis_penomoran',
        'id_parent',
        'row_range_from',
        'row_range_to',
        'info_level',
        'info_left',
        'info_right',
    ];


    const TYPE_NUMBER = [
        self::NUMBER_ALPHABET => 'Alphabet',
        self::NUMBER_NUMBER => 'Number',
        self::NUMBER_ROMAWI => 'Romawi',
    ];

    /**
     * Get the indicator performance reports that owns the IndikatorKolom
     *
     * @return array
     */
    public static function optionsIndikatorBaris(int $id)
    {
        $data = self::where('id_indikator_laporan_kinerja', $id)->get()->pluck('nama', 'id')->toArray();

        return $data;
    }

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'nama' => ['required' => true, 'maxlength' => 255], // Nama Baris
        'id_indikator_laporan_kinerja' => ['required' => true], // Indikator Pengisian
        'jenis_penomoran' => ['required' => false, 'maxlength' => 255, 'options' => self::TYPE_NUMBER], // Jenis Penomoran (A: Alphabet, N: Number, R: Romawi)
        'id_parent' => ['required' => false], //  parent Baris
    ];

    protected static function defineDepthField()
    {
        return 'info_level';
    }
}
