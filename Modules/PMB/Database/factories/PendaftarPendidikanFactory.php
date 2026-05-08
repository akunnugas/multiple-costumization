<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\ProgramStudi;
use Modules\Core\Models\PerguruanTinggi;
use Modules\Core\Models\JenisInstitusi;
use Modules\Core\Models\Wilayah;
use Modules\Core\Models\Sekolah;
use Modules\PMB\Models\Pendaftar;

class PendaftarPendidikanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\PendaftarPendidikan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_pendaftar' => Pendaftar::factory()->create()->id,
            'id_jenjang_pendidikan' => JenjangPendidikan::factory()->create()->id,
            'id_provinsi' => Wilayah::factory()->create()->id,
            'id_kota' => Wilayah::factory()->create()->id,
            'id_jenis_institusi' => JenisInstitusi::factory()->create()->id,
            'nama_institusi' => fake()->name(),
            'jurusan' => fake()->text(50),
            'tahun_lulus' => fake()->year(),
            'id_sekolah' => Sekolah::factory()->create()->id,
            'id_perguruan_tinggi' => PerguruanTinggi::factory()->create()->id,
            'id_program_studi' => ProgramStudi::factory()->create()->id,
            'nisn' => fake()->text(60),
            'nim' => fake()->text(20),
            'nilai' => fake()->randomFloat(2, 1, 100),
            'ipk' => fake()->randomFloat(2, 1, 4),
            'sks' => fake()->randomFloat(0, 1, 144),
        ];
    }
}
