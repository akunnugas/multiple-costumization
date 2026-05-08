<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SPMI\Models\PengisianPanduan;

class IndikatorEvaluasiDiriFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\IndikatorEvaluasiDiri::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_pengisian_panduan' => PengisianPanduan::factory()->create()->id,
            'nomor_indikator' => fake()->text(255),
            'name' => fake()->name(),
            'description' => fake()->text(),
            'is_label' => fake()->boolean(),
            'is_key_point' => fake()->boolean(),
            'is_comment' => fake()->boolean(),
            'is_active' => fake()->boolean(),
            'id_parent' => Parent::factory()->create()->id,
        ];
    }
}
