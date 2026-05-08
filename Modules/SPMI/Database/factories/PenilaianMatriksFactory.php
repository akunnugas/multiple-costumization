<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SPMI\Models\AkreditasiStandar;
use Modules\SPMI\Models\PenilaianPanduan;

class PenilaianMatriksFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\PenilaianMatriks::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_penilaian_panduan' => PenilaianPanduan::factory()->create()->id,
            'id_parent' => null,
            'nomor_penilaian' => fake()->randomNumber(6),
            'kategori_penilaian' => fake()->sentence(),
            'pertanyaan_penilaian' => fake()->sentence(),
            'id_akreditasi_standar' => AkreditasiStandar::factory()->create()->id,
            'apakah_aktif' => fake()->boolean(),
            'jenis_penilaian' => fake()->randomElement([
                'IN', 'PR', 'TL', 'SA'
            ]),
            'bobot_penilaian' => fake()->randomFloat(2, 1, 100),
            'referensi_penilaian' => fake()->sentence(),
            'deskripsi' => fake()->sentence(),
            'apakah_nilai_ditampilkan' => fake()->boolean(),
        ];
    }
}
