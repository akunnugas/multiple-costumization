<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class AkreditasiSyarat extends IndonesianModel
{
    const TYPE_ACCREDITED = 'T';
    const TYPE_RANK = 'P';
    const TYPES = [
        self::TYPE_ACCREDITED => 'Terakreditasi',
        self::TYPE_RANK => 'Peringkat'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.akreditasi_syarat';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_penilaian_panduan',
        'id_penilaian_matriks',
        'id_akreditasi_peringkat',
        'jenis_syarat_akreditasi',
        'nilai_syarat_akreditasi',
        'apakah_data_default',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_penilaian_panduan' => ['required' => true, 'options' => PenilaianPanduan::class], //
        'id_penilaian_matriks' => ['required' => true, 'options' => PenilaianMatriks::class], //
        'id_akreditasi_peringkat' => ['required' => true, 'options' => AkreditasiPeringkat::class], //
        'jenis_syarat_akreditasi' => ['required' => true, 'maxlength' => 2, 'options' => self::TYPES], // Jenis Syarat (T: Terakreditasi, P: Peringkat)
        'nilai_syarat_akreditasi' => ['required' => true], // Syarat Skor
        'apakah_data_default' => ['required' => true, 'boolean' => true],
    ];
}
