<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Gedung;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\JenisRuangan;

class RuanganFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\Ruangan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_ruangan' => fake()->text(255),
            'nama_ruangan' => fake()->name(),
            'lokasi' => fake()->text(255),
            'daya_tampung' => fake()->randomNumber(5),
            'panjang' => fake()->randomNumber(5),
            'lebar' => fake()->randomNumber(5),
            'lantai' => fake()->randomNumber(5),
            'id_unit_kerja' => UnitKerja::factory()->create()->id,
            'id_gedung' => Gedung::factory()->create()->id,
            'id_jenis_ruangan' => JenisRuangan::factory()->create()->id,
        ];
    }
}
