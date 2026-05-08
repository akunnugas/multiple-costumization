<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\DokumenPribadi;

class DokumenPribadiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\DokumenPribadi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $keyStatuses = array_keys(DokumenPribadi::STATUSES);

        return [
            'id_biodata' => \Modules\Core\Models\Biodata::factory()->create()->id,
            'id_dokumen' => \Modules\DMS\Models\Dokumen::factory()->create()->id,
            'id_jenis_dokumen' => \Modules\Core\Models\JenisDokumen::factory()->create()->id,
            'status_dokumen' => fake()->randomElement($keyStatuses),
            'catatan_validasi' => fake()->sentence(),
            'waktu_validasi' => fake()->dateTimeBetween('-1 years', 'now')->format('Y-m-d H:i:sO'),
            'divalidasi_oleh' => \Modules\Gate\Models\User::factory()->create()->id,
        ];
    }
}

