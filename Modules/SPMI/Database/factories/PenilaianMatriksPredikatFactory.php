<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SPMI\Models\PenilaianMatriks;

class PenilaianMatriksPredikatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\PenilaianMatriksPredikat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_penilaian_matriks' => PenilaianMatriks::factory()->create()->id,
            'nilai' => fake()->randomNumber(5),
            'deskripsi' => fake()->text(),
            'kriteria' => fake()->text(100),
            'rumus_penilaian' => fake()->text(100),
            'apakah_nonaktif' => fake()->boolean(),
        ];
    }
}
