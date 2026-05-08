<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PengumumanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\Pengumuman::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'judul_pengumuman' => fake()->text(255),
            'link_pengumuman' => fake()->url(),
            'isi_pengumuman' => fake()->text(),
            'jenis_pengumuman' => fake()->randomElement(\Modules\PMB\Models\Pengumuman::TYPES),
            'apakah_aktif' => fake()->boolean(),
        ];
    }
}
