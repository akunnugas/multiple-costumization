<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SyaratJenisFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\SyaratJenis::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_jenis_syarat' => fake()->unique()->text(10),
            'nama_jenis_syarat' => fake()->name(),
        ];
    }
}
