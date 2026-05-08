<?php

namespace Modules\Litabmas\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\DMS\Models\Dokumen;

class DokumenPetunjukTeknisFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Litabmas\Models\DokumenPetunjukTeknis::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_dokumen' => Dokumen::factory()->create()->id,
            'nama_dokumen_teknis' => fake()->name(),
            'jenis_dokumen_teknis' => fake()->text(100),
            'apakah_aktif' => fake()->boolean(),
        ];
    }
}
