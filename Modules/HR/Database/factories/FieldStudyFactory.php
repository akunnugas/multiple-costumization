<?php

namespace Modules\HR\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FieldStudyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\HR\Models\FieldStudy::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => fake()->randomNumber(5),
            'name' => fake()->word(),
            'parent_id' => null,
            'info_left' => null,
            'info_right' => null,
            'depth' => null,
        ];
    }
}

