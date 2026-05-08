<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Gate\Models\User;
use Modules\PMB\Models\SebaranProdi;
use Modules\PMB\Models\Pendaftar;

class PilihanProdiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\PilihanProdi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_pendaftar' => Pendaftar::factory()->create()->id,
            'id_sebaran_prodi' => SebaranProdi::factory()->create()->id,
            'urutan_pilihan' => fake()->numberBetween(1, 100),
            'status_pilihan' => fake()->word(),
            'nilai_pilihan' => fake()->numberBetween(1, 100),
            'apakah_rekomendasi' => fake()->boolean(),
            'apakah_afirmasi' => fake()->boolean(),
            'apakah_afirmasi_disetujui' => fake()->boolean(),
            'afirmasi_disetujui_oleh' => User::factory()->create()->id,
        ];
    }
}
