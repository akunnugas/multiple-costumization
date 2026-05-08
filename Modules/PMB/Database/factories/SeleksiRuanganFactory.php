<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Ruangan;

class SeleksiRuanganFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\SeleksiRuangan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_ruangan' => fake()->text(255),
            'nama_ruangan' => fake()->name(),
            'daya_tampung' => fake()->randomNumber(5),
            'id_ruangan' => Ruangan::factory()->create()->id,
        ];
    }
}
