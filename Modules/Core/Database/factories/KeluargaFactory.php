<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\StatusHubunganKeluarga;
use Modules\Core\Models\Pekerjaan;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\Penghasilan;

class KeluargaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\Keluarga::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_keluarga' => fake()->text(255),
            'id_biodata' => Biodata::factory()->create()->id,
            'id_status_hubungan_keluarga' => StatusHubunganKeluarga::factory()->create()->id,
            'nik' => fake()->text(255),
            'nama' => fake()->name(),
            'tempat_lahir' => fake()->text(255),
            'tanggal_lahir' => fake()->text(),
            'alamat' => fake()->text(255),
            'telepon' => fake()->text(255),
            'jenis_kelamin' => fake()->text(1),
            'id_jenjang_pendidikan' => JenjangPendidikan::factory()->create()->id,
            'id_pekerjaan' => Pekerjaan::factory()->create()->id,
            'id_penghasilan' => Penghasilan::factory()->create()->id,
        ];
    }
}
