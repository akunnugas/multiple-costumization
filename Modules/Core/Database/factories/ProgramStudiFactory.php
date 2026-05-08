<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\PerguruanTinggi;

class ProgramStudiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\ProgramStudi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_perguruan_tinggi' => PerguruanTinggi::factory()->create()->id,
            'id_jenjang_pendidikan' => JenjangPendidikan::factory()->create()->id,
            'kode_prodi' => fake()->text(20),
            'nama_prodi' => fake()->name(),
            'alamat_prodi' => fake()->text(100),
            'telepon_prodi' => fake()->text(20),
            'kode_sister' => fake()->text(255),
        ];
    }
}
