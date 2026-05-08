<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SPMI\Models\PengisianIndikator;

class DataPengisianLKFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\DataPengisianLK::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'filling_indicator_id' => PengisianIndikator::factory()->create()->id,
            'data' => fake()->text(),
        ];
    }
}
