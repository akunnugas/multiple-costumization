<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\JenisInstitusi;
use Modules\PMB\Models\SebaranProdi;

class SebaranAsalPendaftarFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\SebaranAsalPendaftar::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_sebaran_prodi' => SebaranProdi::factory()->create()->id,
            'id_jenis_institusi' => JenisInstitusi::factory()->create()->id,
        ];
    }
}
