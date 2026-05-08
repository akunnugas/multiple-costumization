<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\DMS\Models\Dokumen;
use Modules\Gate\Models\User;
use Modules\PMB\Models\SeleksiJadwal;
use Modules\PMB\Models\SeleksiJenis;
use Modules\PMB\Models\Pendaftar;

class SeleksiNilaiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\SeleksiNilai::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_pendaftar' => Pendaftar::factory()->create()->id,
            'id_seleksi_jenis' => SeleksiJenis::factory()->create()->id,
            'nilai_seleksi' => fake()->numberBetween(0, 100),
            'apakah_sesuai' => fake()->boolean(),
            'keterangan_nilai' => fake()->sentence(),
            'id_file_lampiran' => Dokumen::factory()->create()->id,
            'id_seleksi_jadwal' => SeleksiJadwal::factory()->create()->id,
            'dinilai_oleh' => User::factory()->create()->id,
        ];
    }
}
