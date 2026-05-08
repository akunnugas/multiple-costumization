<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SPMI\Models\AkreditasiPeringkat;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\PenilaianMatriks;

class AkreditasiSyaratFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\AkreditasiSyarat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'assessment_guide_id' => PenilaianPanduan::factory()->create()->id,
            'id_penilaian_matriks' => PenilaianMatriks::factory()->create()->id,
            'accreditation_rank_id' => AkreditasiPeringkat::factory()->create()->id,
            'type' => fake()->text(2),
            'score' => fake()->randomFloat(2, 1, 100),
        ];
    }
}
