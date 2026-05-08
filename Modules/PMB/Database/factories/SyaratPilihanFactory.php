<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\PMB\Models\Syarat;

class SyaratPilihanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\SyaratPilihan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_syarat' => Syarat::factory()->create()->id,
            'nama_pilihan' => fake()->name(),
            'poin_pilihan' => fake()->randomFloat(2, 1, 100),
        ];
    }
}
