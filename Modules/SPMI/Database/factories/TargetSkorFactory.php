<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SPMI\Models\TargetIndikator;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksPredikat;

class TargetSkorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\TargetSkor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_target_indikator' => TargetIndikator::factory()->create()->id,
            'id_penilaian_matriks' => PenilaianMatriks::factory()->create()->id,
            'id_predikat_matriks_penilaian' => PenilaianMatriksPredikat::factory()->create()->id,
            'nilai_default' => fake()->randomFloat(2, 1, 100),
            'nilai' => fake()->randomFloat(2, 1, 100),
        ];
    }
}
