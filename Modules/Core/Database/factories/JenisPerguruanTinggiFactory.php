<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class JenisPerguruanTinggiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\JenisPerguruanTinggi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_jenis_pt' => Str::password(10, true, false, false),
            'nama_jenis_pt' => fake()->word(),
            'kategori' => fake()->word()
        ];
    }
}

