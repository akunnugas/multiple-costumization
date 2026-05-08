<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\JenisPendaftaran;

class JenisPendaftaranFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\JenisPendaftaran::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $codeLists = JenisPendaftaran::CODE_LISTS;

        return [
            'kode_jenis_pendaftaran' => fake()->unique()->randomElement(array_keys($codeLists)),
            'nama_jenis_pendaftaran' => fake()->sentence(),
        ];
    }
}

