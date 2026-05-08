<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\DMS\Models\Dokumen;
use Modules\SPMI\Models\SpmiJenisDokumen;

class SpmiDokumenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\SpmiDokumen::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_spmi_dokumen' => $this->faker->randomNumber(5),
            'nama_spmi_dokumen' => $this->faker->sentence(),
            'deskripsi' => $this->faker->sentence(),
            'id_dokumen' => Dokumen::factory(),
            'versi' => $this->faker->randomDigit(),
            'id_jenis' => SpmiJenisDokumen::factory(),
            'apakah_aktif' => $this->faker->boolean(),
            'tanggal_awal_berlaku' => $this->faker->date(),
            'tanggal_akhir_berlaku' => $this->faker->date(),
        ];
    }
}
