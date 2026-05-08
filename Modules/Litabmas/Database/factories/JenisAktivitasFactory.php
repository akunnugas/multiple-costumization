<?php

namespace Modules\Litabmas\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class JenisAktivitasFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Litabmas\Models\JenisAktivitas::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nama_jenis_aktivitas' => "Aktivitas {$this->faker->unique()->word}",
        ];
    }
}
