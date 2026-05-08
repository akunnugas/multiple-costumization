<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AktivitasFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\Aktivitas::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nama_aktivitas' => $this->faker->unique()->word(),
            'kode_aktivitas' => $this->faker->unique()->regexify('[A-Za-z0-9]{10}'),
            'urutan_aktivitas' => $this->faker->randomNumber(),
        ];
    }
}
