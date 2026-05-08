<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SyaratFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\Syarat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_syarat' => fake()->unique()->text(10),
            'nama_syarat' => 'Syarat ' . fake()->text(10),
            'poin_syarat' => fake()->numberBetween(1, 100),
        ];
    }
}
