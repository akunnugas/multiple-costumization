<?php

namespace Modules\Core\Database\factories\Shared;

use Illuminate\Database\Eloquent\Factories\Factory;

class PerguruanTinggiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\Shared\PerguruanTinggi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_pt' => fake()->text(255),
            'nama_pt' => fake()->name(),
            'alamat_pt' => fake()->text(255),
            'telepon_pt' => fake()->text(255),
            'kode_sister' => fake()->text(255),
        ];
    }
}
