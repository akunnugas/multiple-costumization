<?php

namespace Modules\Litabmas\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Litabmas\Models\JenisPublikasi;

class JenisOutcomePenelitianFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Litabmas\Models\JenisOutcomePenelitian::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $categories = array_keys(\Modules\Litabmas\Models\JenisOutcomePenelitian::CATEGORIES);
        $collectionLimits = array_keys(\Modules\Litabmas\Models\JenisOutcomePenelitian::COLLECTION_LIMIT_OPTIONS);

        // cek jika memiliki data jenis publikasi
        $idJenisPublikasi = JenisPublikasi::query()->inRandomOrder()->first()?->id;
        if (!$idJenisPublikasi) {
            $idJenisPublikasi = JenisPublikasi::factory()->create()->id;
        }

        return [
            'id_jenis_publikasi' => $idJenisPublikasi,
            'nama_outcome' => fake()->unique()->name(),
            'kategori_outcome' => fake()->randomElement($categories),
            'batas_pengumpulan_outcome' => fake()->randomElement($collectionLimits),
        ];
    }
}
