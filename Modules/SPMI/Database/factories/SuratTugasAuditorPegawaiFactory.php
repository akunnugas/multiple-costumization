<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\Biodata;
use Modules\SPMI\Models\SuratTugasAuditor;

class SuratTugasAuditorPegawaiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\SuratTugasAuditorPegawai::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_surat_tugas_auditor' => SuratTugasAuditor::factory()->create()->id,
            'id_personil' => Biodata::factory()->create()->id,
            'id_unit' => UnitKerja::factory()->create()->id,
            'posisi' => fake()->text(1),
        ];
    }
}
