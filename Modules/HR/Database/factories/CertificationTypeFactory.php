<?php

namespace Modules\HR\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CertificationTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\HR\Models\CertificationType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'feeder_id' => fake()->randomNumber(2),
            'dikti_id' => fake()->randomNumber(2),
            'sister_id' => fake()->uuid(),
            'code' => fake()->randomElement(['S', 'D']) . fake()->randomNumber(4),
            'name' => fake()->word()
        ];
    }
}
