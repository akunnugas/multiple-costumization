<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\PMB\Models\PenilaianRapor;
use Modules\PMB\Models\MataPelajaran;

class PenilaianRaporFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PenilaianRapor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_mata_pelajaran' => MataPelajaran::factory()->create()->id,
            'kode_penilaian' => fake()->unique()->randomNumber(5),
            'nama_penilaian' => fake()->text(255),
            'jenis_penilaian' => fake()->randomElement(PenilaianRapor::TYPES),
            'keterangan_penilaian' => fake()->text(500),
        ];
    }
}
