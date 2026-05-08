<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PerguruanTinggiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\PerguruanTinggi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_pt' => Str::password(20, false, true, false),
            'nama_pt' => fake()->word(),
            'alamat_pt' => fake()->address(),
            'telepon_pt' => fake()->phoneNumber(),
            'ref_key_siakad' => fake()->word(),
        ];
    }
}
