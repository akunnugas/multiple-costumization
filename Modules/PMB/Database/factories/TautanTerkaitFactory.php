<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TautanTerkaitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\TautanTerkait::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nama_tautan' => fake()->name(),
            'link_tautan' => fake()->url(),
            'urutan_tautan' => fake()->randomNumber(5),
        ];
    }
}
