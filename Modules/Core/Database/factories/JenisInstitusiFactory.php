<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\JenjangPendidikan;

class JenisInstitusiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\JenisInstitusi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_jenis_institusi' => fake()->text(255),
            'nama_jenis_institusi' => fake()->name(),
            'id_jenjang_pendidikan' => JenjangPendidikan::factory()->create()->id,
        ];
    }
}
