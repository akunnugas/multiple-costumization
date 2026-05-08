<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Kampus;

class GedungFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\Gedung::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_gedung' => fake()->text(255),
            'nama_gedung' => fake()->name(),
            'alamat_gedung' => fake()->text(255),
            'telepon_gedung' => fake()->text(255),
            'jumlah_lantai' => fake()->randomNumber(5),
            'jumlah_ruangan' => fake()->randomNumber(5),
            'id_kampus' => Kampus::factory()->create()->id,
        ];
    }
}
