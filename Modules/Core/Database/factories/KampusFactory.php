<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class KampusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\Kampus::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_kampus' => fake()->text(255),
            'nama_kampus' => fake()->name(),
            'alamat_kampus' => fake()->text(255),
            'telepon_kampus' => fake()->text(255),
        ];
    }
}
