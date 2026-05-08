<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\JenisInstitusi;
use Modules\Core\Models\Wilayah;

class SekolahFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\Sekolah::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'npsn' => fake()->unique()->numberBetween(1000000000, 9999999999),
            'id_kota' => Wilayah::factory()->create()->id,
            'id_jenis_institusi' => JenisInstitusi::factory()->create()->id,
            'nama_sekolah' => fake()->name(),
            'alamat_sekolah' => fake()->text(255),
            'rt_sekolah' => fake()->text(255),
            'rw_sekolah' => fake()->text(255),
            'kode_pos_sekolah' => fake()->text(255),
            'telepon_sekolah' => fake()->text(255),
            'email_sekolah' => strtolower(fake()->firstName() . '.' . fake()->lastName()) . '@email.com',
            'website_sekolah' => fake()->text(255),
            'akreditasi' => fake()->text(255),
        ];
    }
}
