<?php

namespace Modules\HR\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HR\Models\JabatanAkademik;
use Modules\HR\Models\PositionLevel;

class FunctionalPositionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\HR\Models\FunctionalPosition::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'academic_position_id' => JabatanAkademik::factory()->create()->id,
            'position_level_id' => PositionLevel::factory()->create()->id,
            'code' => fake()->unique()->randomNumber(5),
            'name' => fake()->name(),
            'credit_number' => fake()->randomNumber(2),
            'retirement_age' => fake()->randomNumber(2),
            'position_emis_code' => fake()->randomNumber(5),
            'dikti_id' => fake()->randomNumber(2),
        ];
    }
}
