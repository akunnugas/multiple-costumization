<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Core\Models\Suku;
use Modules\Core\Models\Pekerjaan;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\Wilayah;
use Modules\Core\Models\Agama;
use Modules\Gate\Models\User;

class BiodataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\Biodata::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $baseTimestamp = date('Y-m-d H:i:sO');
        $birthDate = date('Y-m-d', strtotime('-17 Years', strtotime($baseTimestamp)));
        $gender = Biodata::JENIS_KELAMIN;

        return [
            'id_user' => User::factory()->create()->id,
            'nama' => fake()->name(),
            'gelar_depan' => fake()->word(),
            'gelar_belakang' => fake()->word(),
            'tanggal_lahir' => $birthDate,
            'tempat_lahir' => fake()->city(),
            'jenis_kelamin' => fake()->randomElement(array_keys($gender)),
            'id_agama' => Agama::factory()->create()->id,
            'id_suku' => Suku::factory()->create()->id,
            'id_negara' => Wilayah::factory()->create()->id,
            'id_provinsi' => Wilayah::factory()->create()->id,
            'id_kota' => Wilayah::factory()->create()->id,
            'id_kecamatan' => Wilayah::factory()->create()->id,
            'desa' => fake()->streetName(),
            'dusun' => fake()->streetName(),
            'alamat' => fake()->address(),
            'rt' => fake()->numberBetween(1, 100),
            'rw' => fake()->numberBetween(1, 100),
            'kode_pos' => fake()->postcode(),
            'nik' => Str::password(10, false, true, false),
            'no_kk' => Str::password(10, false, true, false),
            'email' => fake()->email(),
            'telepon' => Str::password(10, false, true, false),
            'npsn' => Str::password(10, false, true, false),
            'no_kps' => Str::password(10, false, true, false),
            'waktu_validasi' => $baseTimestamp,
            'berat' => fake()->numberBetween(1, 200),
            'tinggi' => fake()->numberBetween(1, 300),
            'no_paspor' => Str::password(10, false, true, false),
            'id_pekerjaan' => Pekerjaan::factory()->create()->id,
            'nama_instansi' => fake()->company(),
            'ukuran_seragam' => fake()->randomElement(['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL', '5XL']),
            'nama_ponpes' => fake()->company(),
            'alamat_ponpes' => fake()->address(),
            'lama_ponpes' => fake()->numberBetween(1, 10),
        ];
    }
}
