<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PenghasilanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\Penghasilan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_penghasilan' => fake()->text(255),
            'nama_penghasilan' => fake()->name(),
            'poin_kip' => fake()->text(255),
            'kode_emis' => fake()->text(255),
        ];
    }
}
