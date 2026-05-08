<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class JenjangPendidikanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\JenjangPendidikan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // buat name berdasarkan degree
//        $degreeCode = fake()->unique()->randomElement([
//            'S1', 'S2', 'S3', 'D3', 'D4', 'Profesi', 'Spesialis', 'Sp1', 'Sp2', 'Sp3'
//        ]);
        // degree code maksimal 10 huruf
        $degreeCode = fake()->unique()->word();

        return [
            'nama_jenjang' => fake()->word(),
            'nama_jenjang_en' => fake()->word(),
            'kode_jenjang' => strlen($degreeCode) > 10 ? substr($degreeCode, 0, 10) : $degreeCode,
            'kode_dikti' => fake()->randomNumber(),
            'apakah_akademik' => fake()->randomElement([true, false]),
            'apakah_pt' => fake()->randomElement([true, false]),
            'apakah_pasca' => fake()->randomElement([true, false]),
            'kode_emis' => fake()->randomNumber(),
            'kode_emis_pasca' => fake()->randomNumber(),
            'kode_emis_dosen' => fake()->randomNumber(),
            'urutan' => fake()->randomNumber(),
        ];
    }
}
