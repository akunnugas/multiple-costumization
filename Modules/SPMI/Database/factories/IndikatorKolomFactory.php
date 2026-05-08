<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class IndikatorKolomFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\IndikatorKolom::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_indikator_laporan_kinerja' => \Modules\SPMI\Models\IndikatorLaporanKinerja::factory(),
            'jenis_form' => fake()->randomElement(['X','C','D','S','H','N','A','B']),
            'jenis_kolom' => fake()->randomElement(['I','B','J','R','A']),
            'posisi_kolom' => fake()->randomElement(['H','V']),
            'apakah_terlihat' => true,
        ];
    }
}
