<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SPMI\Models\DataPengisianLED;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\PengisianPanduan;

class IndikatorReferensiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\IndikatorReferensi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_indikator_evaluasi_diri' => DataPengisianLED::factory()->create()->id,
            'id_pengisian_panduan' => PengisianPanduan::factory()->create()->id,
            'id_indikator_laporan_kinerja' => IndikatorLaporanKinerja::factory()->create()->id,
        ];
    }
}
