<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SeleksiJenisFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\SeleksiJenis::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_jenis_seleksi' => fake()->word(),
            'nama_jenis_seleksi' => fake()->sentence(),
        ];
    }
}

