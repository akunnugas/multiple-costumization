<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AkreditasiStandarFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\AkreditasiStandar::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => substr(fake()->word(), 0, 3),
            'name' => fake()->word(),
            // get id from standard type
            'standard_type_id' => \Modules\SPMI\Models\JenisStandar::factory()->create()->id,
        ];
    }
}
