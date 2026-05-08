<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class KontenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\Konten::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'judul_konten' => fake()->text(255),
            'isi_konten' => fake()->text(),
            'informasi_tambahan' => fake()->text(255),
            'jenis_konten' => fake()->text(10),
            'id_file_gambar' => Image::factory()->create()->id,
        ];
    }
}
