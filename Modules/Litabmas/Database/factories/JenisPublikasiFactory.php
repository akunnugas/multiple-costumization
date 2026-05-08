<?php

namespace Modules\Litabmas\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class JenisPublikasiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Litabmas\Models\JenisPublikasi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nama_jenis_publikasi' => fake()->name(),
            'ref_key_siakad' => fake()->unique()->regexify('[A-Za-z0-9]{10}'),
        ];
    }
}
