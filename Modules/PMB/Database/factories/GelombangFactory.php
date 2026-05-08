<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GelombangFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\Gelombang::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nama_gelombang' => fake()->sentence(),
        ];
    }
}
