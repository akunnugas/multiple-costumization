<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SPMI\Models\IndikatorLaporanKinerja;

class IndikatorBarisFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\IndikatorBaris::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => fake()->name(),
            'id_indikator_laporan_kinerja' => IndikatorLaporanKinerja::factory()->create()->id,
            'jenis_penomoran' => fake()->text(255),
            'id_parent' => Parent::factory()->create()->id,
        ];
    }
}
