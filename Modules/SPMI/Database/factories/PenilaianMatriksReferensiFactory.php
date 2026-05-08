<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;

class PenilaianMatriksReferensiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\PenilaianMatriksReferensi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_penilaian_matriks' => PenilaianMatriks::factory()->create()->id,
            'id_butir_referensi' => IndikatorEvaluasiDiri::factory()->create()->id,
            'jenis_referensi' => fake()->text(255),
        ];
    }
}
