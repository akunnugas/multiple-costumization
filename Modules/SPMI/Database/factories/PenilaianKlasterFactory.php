<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PenilaianKlasterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\PenilaianKlaster::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => substr(fake()->word(), 0, 3),
            'name' => fake()->sentence(),
        ];
    }
}
