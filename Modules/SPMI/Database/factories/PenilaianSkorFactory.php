<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Modules\SPMI\Models\PenilaianMatriks;

class PenilaianSkorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\PenilaianSkor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'assessment_id' => PenilaianAudit::factory()->create()->id,
            'id_penilaian_matriks' => PenilaianMatriks::factory()->create()->id,
            'assessment_matrix_score_id' => PenilaianMatriksPredikat::factory()->create()->id,
            'default_score' => fake()->randomFloat(2, 1, 100),
            'score' => fake()->randomFloat(2, 1, 100),
            'final_score' => fake()->randomFloat(2, 1, 100),
            'status' => fake()->text(2),
            'feedback' => fake()->text(),
        ];
    }
}
