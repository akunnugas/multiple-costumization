<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AkreditasiBukuFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\AkreditasiBuku::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_buku' => substr(fake()->word(), 0, 3),
            'nama_buku' => fake()->word(),
            'jenis_buku' => fake()->randomElement(['pr', 'se']),
        ];
    }
}
